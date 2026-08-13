"""Generate one polished PDF for every material in the Buku Saku database."""

from __future__ import annotations

import html
import json
import re
import subprocess
import sys
from html.parser import HTMLParser
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "uploads" / "buku_saku" / "materi_pdf"
METADATA_PATH = ROOT / "tmp" / "pdfs" / "material_pdf_metadata.json"
ARIAL = Path(r"C:\Windows\Fonts\arial.ttf")
ARIAL_BOLD = Path(r"C:\Windows\Fonts\arialbd.ttf")


def clean_text(value: str) -> str:
    """Normalise HTML entities and legacy UTF-8 text stored as Latin-1."""
    value = html.unescape(value).replace("\u00a0", " ")
    if any(marker in value for marker in ("Ã", "Â", "â")):
        try:
            value = value.encode("latin-1").decode("utf-8")
        except (UnicodeEncodeError, UnicodeDecodeError):
            pass
    return value


class MaterialHtmlParser(HTMLParser):
    block_tags = {"p", "div", "section", "article", "h1", "h2", "h3", "h4", "h5", "h6", "li", "tr", "br"}
    heading_tags = {"h1", "h2", "h3", "h4", "h5", "h6"}

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.parts: list[tuple[str, str]] = []
        self.buffer: list[str] = []
        self.current_kind = "body"

    def flush(self) -> None:
        text = " ".join("".join(self.buffer).split())
        if text:
            self.parts.append((self.current_kind, text))
        self.buffer = []
        self.current_kind = "body"

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        tag = tag.lower()
        if tag == "br":
            self.flush()
        elif tag in self.block_tags:
            self.flush()
            if tag in self.heading_tags:
                self.current_kind = "heading"
            elif tag == "li":
                self.current_kind = "bullet"

    def handle_endtag(self, tag: str) -> None:
        if tag.lower() in self.block_tags:
            self.flush()

    def handle_data(self, data: str) -> None:
        self.buffer.append(clean_text(data))

    def parsed(self) -> list[tuple[str, str]]:
        self.flush()
        return self.parts


def slugify(value: str) -> str:
    value = clean_text(value).lower()
    value = re.sub(r"[^a-z0-9]+", "-", value)
    return value.strip("-") or "kategori"


def material_parts(source: str) -> list[tuple[str, str]]:
    parser = MaterialHtmlParser()
    parser.feed(source)
    parts = parser.parsed()
    return parts or [("body", "Materi belum memiliki isi.")]


def decorate_page(canvas, document) -> None:
    canvas.saveState()
    width, height = A4
    canvas.setFillColor(colors.HexColor("#0B4F9C"))
    canvas.rect(0, height - 18 * mm, width, 18 * mm, stroke=0, fill=1)
    canvas.setFillColor(colors.white)
    canvas.setFont("Arial-Bold", 9)
    canvas.drawString(18 * mm, height - 11.5 * mm, "BUKU SAKU SATPAM - BNN TULUNGAGUNG")
    canvas.setFillColor(colors.HexColor("#6B7280"))
    canvas.setFont("Arial", 8)
    canvas.drawString(18 * mm, 12 * mm, "Sistem Informasi Buku Mutasi Satpam")
    canvas.drawRightString(width - 18 * mm, 12 * mm, f"Halaman {document.page}")
    canvas.restoreState()


def build_pdf(material: dict) -> tuple[str, int]:
    title = clean_text(material["judul"])
    category = clean_text(material["nama_kategori"])
    category_dir = OUTPUT_ROOT / f"{int(material['id_kategori']):02d}-{slugify(category)}"
    category_dir.mkdir(parents=True, exist_ok=True)
    filename = f"materi-{int(material['id_materi']):03d}.pdf"
    output_path = category_dir / filename

    document = SimpleDocTemplate(
        str(output_path),
        pagesize=A4,
        leftMargin=18 * mm,
        rightMargin=18 * mm,
        topMargin=28 * mm,
        bottomMargin=22 * mm,
        title=title,
        author="BNN Tulungagung",
    )
    styles = getSampleStyleSheet()
    title_style = ParagraphStyle(
        "MaterialTitle", parent=styles["Title"], fontName="Arial-Bold", fontSize=20,
        leading=25, textColor=colors.HexColor("#0B4F9C"), alignment=TA_LEFT, spaceAfter=4 * mm,
    )
    category_style = ParagraphStyle(
        "Category", parent=styles["Normal"], fontName="Arial", fontSize=10,
        leading=14, textColor=colors.HexColor("#4B5563"), spaceAfter=8 * mm,
    )
    body_style = ParagraphStyle(
        "MaterialBody", parent=styles["BodyText"], fontName="Arial", fontSize=10.5,
        leading=16, textColor=colors.HexColor("#1F2937"), spaceAfter=3.5 * mm,
    )
    heading_style = ParagraphStyle(
        "MaterialHeading", parent=body_style, fontName="Arial-Bold", fontSize=12,
        leading=16, textColor=colors.HexColor("#0B4F9C"), spaceBefore=3 * mm, spaceAfter=3 * mm,
    )
    bullet_style = ParagraphStyle(
        "MaterialBullet", parent=body_style, leftIndent=7 * mm, firstLineIndent=-4 * mm,
    )

    story = [
        Paragraph(html.escape(title), title_style),
        Paragraph(f"Kategori: {html.escape(category)}", category_style),
    ]
    for kind, text in material_parts(material.get("isi") or ""):
        escaped = html.escape(text)
        if kind == "heading":
            story.append(Paragraph(escaped, heading_style))
        elif kind == "bullet":
            story.append(Paragraph(f"- {escaped}", bullet_style))
        else:
            story.append(Paragraph(escaped, body_style))
    story.append(Spacer(1, 2 * mm))

    document.build(story, onFirstPage=decorate_page, onLaterPages=decorate_page)
    relative_path = output_path.relative_to(ROOT).as_posix()
    return relative_path, output_path.stat().st_size


def main() -> int:
    if not ARIAL.is_file() or not ARIAL_BOLD.is_file():
        raise RuntimeError("Font Arial Windows tidak ditemukan.")
    pdfmetrics.registerFont(TTFont("Arial", str(ARIAL)))
    pdfmetrics.registerFont(TTFont("Arial-Bold", str(ARIAL_BOLD)))

    export = subprocess.run(
        ["php", "tools/export_materials_for_pdf.php"], cwd=ROOT, capture_output=True, text=True, check=False
    )
    if export.returncode != 0:
        raise RuntimeError(export.stderr.strip() or "Materi tidak dapat diekspor dari database.")
    materials = json.loads(export.stdout)
    if not materials:
        raise RuntimeError("Tidak ada materi buku saku untuk dibuatkan PDF.")

    metadata = []
    for material in materials:
        pdf_path, pdf_size = build_pdf(material)
        metadata.append({"id_materi": int(material["id_materi"]), "pdf_path": pdf_path, "pdf_size": pdf_size})

    METADATA_PATH.parent.mkdir(parents=True, exist_ok=True)
    METADATA_PATH.write_text(json.dumps(metadata, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"{len(metadata)} PDF materi dibuat di {OUTPUT_ROOT.relative_to(ROOT)}")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as error:
        print(f"Gagal membuat PDF materi: {error}", file=sys.stderr)
        raise SystemExit(1)
