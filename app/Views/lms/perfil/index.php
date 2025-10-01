<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/perfil.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>
    <main class="perfil-container">
        <div class="perfil-card">
            <div class="perfil-foto">
                <img src="<?= isset($usuario['foto']) && !empty($usuario['foto'])
                    ? esc($usuario['foto'])
                    : base_url('assets/img/user-default.jpg') ?>" alt="Foto de perfil">

                <form action="<?= base_url('perfil/actualizarFoto') ?>" method="post" enctype="multipart/form-data">
                    <label for="foto" class="btn-foto">Cambiar Foto</label>
                    <input type="file" name="foto" id="foto" accept="image/*" hidden>
                </form>
            </div>
            <div class="perfil-datos">
                <h3>Datos Generales</h3>
                <div class="campo">
                    <label>Nombre:</label>
                    <span><?= esc($usuario['nombre']) ?></span>
                </div>
                <div class="campo">
                    <label>Apellido Paterno:</label>
                    <span><?= esc($usuario['apellido_paterno']) ?></span>
                </div>
                <div class="campo">
                    <label>Apellido Materno:</label>
                    <span><?= esc($usuario['apellido_materno']) ?></span>
                </div>
                <div class="campo">
                    <label>Email:</label>
                    <span><?= esc($usuario['email']) ?></span>
                </div>
                <div class="campo">
                    <label><?= $usuario['matricula'] ? 'Matrícula:' : 'Número de empleado:' ?></label>
                    <span><?= esc($usuario['matricula'] ?? $usuario['num_empleado']) ?></span>
                </div>
                <div class="campo">
                    <label>Rol:</label>
                    <span><?= esc(session('rol')) ?></span>
                </div>
            </div>
            <div class="perfil-password">
                <h3>Cambiar Contraseña</h3>
                <form action="<?= base_url('perfil/actualizarPassword') ?>" method="post">
                    <input type="password" name="password" placeholder="Nueva contraseña" required>
                    <input type="password" name="confirmar" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn-guardar">Guardar</button>
                </form>
            </div>
            <div class="perfil-extra">
                <h3>Información Personal (Próximamente)</h3>
                <p class="texto-muted">Esta sección estará disponible para actualizar tus datos personales.</p>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.getElementById("foto");
            const preview = document.querySelector(".perfil-foto img");
            const btnLabel = document.querySelector(".btn-foto");

            fileInput.addEventListener("change", function (event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    Swal.fire({
                        title: 'Recorta tu imagen',
                        html: `
                        <div style="width:100%;max-width:300px;margin:auto;">
                            <img id="cropImage" src="${e.target.result}" style="width:100%;border-radius:10px;"/>
                        </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'Subir a Cloudinary',
                        didOpen: () => {
                            const image = document.getElementById('cropImage');
                            const cropper = new Cropper(image, {
                                aspectRatio: 1, // 🔸 cuadrado
                                viewMode: 1,
                                dragMode: 'move',
                                background: false,
                                autoCropArea: 1,
                            });

                            Swal.getConfirmButton().addEventListener('click', async () => {
                                const canvas = cropper.getCroppedCanvas({
                                    width: 300,
                                    height: 300,
                                });

                                // Deshabilitar botón y poner loader
                                btnLabel.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Subiendo...';
                                btnLabel.classList.add("disabled");

                                // Aplicar blur visual
                                preview.style.filter = "blur(2px)";
                                preview.style.opacity = "0.6";

                                // Convertir a blob
                                canvas.toBlob(async (blob) => {
                                    const formData = new FormData();
                                    formData.append('foto', blob, 'perfil.jpg');

                                    Swal.fire({
                                        title: 'Subiendo foto...',
                                        text: 'Por favor espera unos segundos',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });

                                    try {
                                        const response = await fetch("<?= base_url('perfil/subirFotoCloud') ?>", {
                                            method: "POST",
                                            body: formData
                                        });

                                        const result = await response.json();
                                        Swal.close();

                                        // Restaurar botón
                                        btnLabel.innerHTML = 'Cambiar Foto';
                                        btnLabel.classList.remove("disabled");

                                        if (result.success) {
                                            // Transición suave al cambiar la imagen
                                            preview.style.transition = "opacity 0.4s ease";
                                            preview.style.opacity = "0";
                                            setTimeout(() => {
                                                preview.src = result.url;
                                                preview.style.filter = "none";
                                                preview.style.opacity = "1";
                                            }, 300);

                                            Swal.fire('✅ Foto actualizada', 'Tu foto fue subida correctamente.', 'success');
                                        } else {
                                            preview.style.filter = "none";
                                            preview.style.opacity = "1";
                                            Swal.fire('⚠️ Error', result.message, 'error');
                                        }
                                    } catch (err) {
                                        preview.style.filter = "none";
                                        preview.style.opacity = "1";
                                        Swal.close();
                                        Swal.fire('❌ Error inesperado', 'Hubo un problema al subir la imagen.', 'error');
                                    }
                                }, 'image/jpeg');
                            });
                        }
                    });
                };
                reader.readAsDataURL(file);
            });
        });
    </script>


</body>

</html>