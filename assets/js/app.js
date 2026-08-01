/*
|--------------------------------------------------------------------------
| Buku Mutasi Satpam
| Global JavaScript
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", () => {

    console.log("Buku Mutasi Satpam Ready");

    // Auto close alert Bootstrap
    const alerts = document.querySelectorAll(".alert");

    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 4000);
    });

});


/*
|--------------------------------------------------------------------------
| SweetAlert
|--------------------------------------------------------------------------
*/

function showSuccess(message){

    Swal.fire({
        icon:'success',
        title:'Berhasil',
        text:message,
        confirmButtonColor:'#2D5BFF'
    });

}

function showError(message){

    Swal.fire({
        icon:'error',
        title:'Oops...',
        text:message,
        confirmButtonColor:'#2D5BFF'
    });

}

function showWarning(message){

    Swal.fire({
        icon:'warning',
        title:'Peringatan',
        text:message,
        confirmButtonColor:'#2D5BFF'
    });

}


/*
|--------------------------------------------------------------------------
| Konfirmasi Hapus
|--------------------------------------------------------------------------
*/

function confirmDelete(url){

    Swal.fire({

        title:'Hapus Data?',

        text:'Data yang dihapus tidak dapat dikembalikan.',

        icon:'warning',

        showCancelButton:true,

        confirmButtonColor:'#dc3545',

        cancelButtonColor:'#6c757d',

        confirmButtonText:'Ya, Hapus',

        cancelButtonText:'Batal'

    }).then((result)=>{

        if(result.isConfirmed){

            window.location.href=url;

        }

    });

}

/*
|--------------------------------------------------------------------------
| Sidebar Overlay Toggle
|--------------------------------------------------------------------------
*/

function initSidebarToggle() {

    const sidebar = document.querySelector(".sidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");
    const backdrop = document.getElementById("sidebarBackdrop");

    if (!sidebar || !sidebarToggle) {
        return;
    }

    const setToggleState = (isOpen) => {
        const icon = sidebarToggle.querySelector("i");
        sidebarToggle.classList.toggle("is-active", isOpen);
        sidebarToggle.setAttribute("aria-expanded", String(isOpen));
        sidebarToggle.setAttribute("aria-label", isOpen ? "Tutup menu navigasi" : "Buka menu navigasi");
        if (icon) {
            icon.classList.toggle("bi-list", !isOpen);
            icon.classList.toggle("bi-x-lg", isOpen);
        }
    };

    const closeSidebar = () => {
        sidebar.classList.remove("show");
        setToggleState(false);
        if (backdrop) {
            backdrop.classList.remove("show");
        }
    };

    const openSidebar = () => {
        sidebar.classList.add("show");
        setToggleState(true);
        if (backdrop) {
            backdrop.classList.add("show");
        }
    };

    sidebarToggle.addEventListener("click", () => {
        if (sidebar.classList.contains("show")) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    if (backdrop) {
        backdrop.addEventListener("click", closeSidebar);
    }

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeSidebar();
        }
    });
}

initSidebarToggle();
