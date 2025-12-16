// assets/js/script.js

// 🖼️ Preview foto sebelum upload
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.querySelector('input[name="foto"]');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('foto-preview');

    if (fotoInput && previewContainer && previewImage) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Cek tipe file (client-side)
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Hanya file JPG/PNG yang diizinkan!');
                    fotoInput.value = ''; // reset
                    previewContainer.style.display = 'none';
                    return;
                }

                // Cek ukuran (client-side)
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('Ukuran file maksimal 2 MB!');
                    fotoInput.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }

                // Tampilkan preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }

    // ✅ Validasi form tambahan (opsional)
    const formPengaduan = document.getElementById('form-pengaduan');
    if (formPengaduan) {
        formPengaduan.addEventListener('submit', function(e) {
            const judul = document.querySelector('[name="judul"]').value.trim();
            const deskripsi = document.querySelector('[name="deskripsi"]').value.trim();

            if (judul.length < 5) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Judul terlalu pendek',
                    text: 'Judul minimal 5 karakter.',
                    confirmButtonText: 'Oke'
                });
                return false;
            }

            if (deskripsi.length < 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Deskripsi terlalu singkat',
                    text: 'Deskripsi minimal 10 karakter agar jelas.',
                    confirmButtonText: 'Oke'
                });
                return false;
            }
        });
    }
});

// 🔔 Fungsi notifikasi manual (bisa dipakai di mana saja)
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: 2500,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: message
    });
}

// 🔔 Notifikasi Toast (pojok kanan atas)
function showToast(message, type = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: type,
        title: message,
        padding: '0.75rem 1rem',
        customClass: {
            popup: 'shadow-lg'
        }
    });
}

// ✅ Auto-toast saat ada session 'swal_success' atau 'swal_error'
document.addEventListener('DOMContentLoaded', function() {
    // Cek session via hidden input (karena JS tidak bisa baca $_SESSION langsung)
    const successMsg = document.getElementById('swal-success-msg');
    const errorMsg = document.getElementById('swal-error-msg');
    
    if (successMsg) {
        showToast(successMsg.value, 'success');
    }
    if (errorMsg) {
        showToast(errorMsg.value, 'error');
    }
});