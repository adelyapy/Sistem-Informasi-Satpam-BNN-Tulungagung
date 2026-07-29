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

const sidebar = document.querySelector(".sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");

if (sidebar && sidebarToggle) {

    sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("show");
    });

}