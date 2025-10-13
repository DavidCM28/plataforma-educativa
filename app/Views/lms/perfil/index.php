<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="rol-usuario" content="<?= strtolower(session('rol')) ?>">
    <title><?= esc($title) ?></title>

    <!-- 🎨 Estilos -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/perfil.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
</head>

<body>

    <?= $this->include('layouts/header-plataforma') ?>
    <?= $this->include('layouts/sidebar-plataforma') ?>

    <main class="content-dark perfil-container">
        <div class="perfil-card">

            <!-- 🖼️ FOTO -->
            <div class="perfil-foto">
                <img src="<?= isset($usuario['foto']) && !empty($usuario['foto'])
                    ? esc($usuario['foto'])
                    : base_url('assets/img/user-default.jpg') ?>" alt="Foto de perfil" id="profileImage">

                <form id="formCambiarFoto" action="<?= base_url('perfil/actualizarFoto') ?>" method="post"
                    enctype="multipart/form-data">
                    <label for="foto" class="btn-foto">Cambiar Foto</label>
                    <input type="file" name="foto" id="foto" accept="image/*" hidden>
                </form>
            </div>

            <!-- 📋 INFORMACIÓN GENERAL Y DETALLADA -->
            <div class="perfil-extra">
                <h3>Mi Información</h3>

                <!-- 📑 Navegación -->
                <div class="tabs">
                    <button class="tab-btn active" data-tab="tab0"><i class="fa fa-id-card"></i> Generales</button>
                    <button class="tab-btn" data-tab="tab1"><i class="fa fa-user"></i> Personales</button>
                    <button class="tab-btn" data-tab="tab2"><i class="fa fa-heartbeat"></i> Médicos</button>
                    <button class="tab-btn" data-tab="tab3"><i class="fa fa-home"></i> Domicilio</button>
                    <button class="tab-btn" data-tab="tab4"><i class="fa fa-phone"></i> Comunicación</button>
                    <button class="tab-btn" data-tab="tab5"><i class="fa fa-graduation-cap"></i> Formación</button>
                    <button class="tab-btn" data-tab="tab6"><i class="fa fa-lock"></i> Contraseña</button>
                </div>

                <!-- 🧾 FORMULARIO GENERAL -->
                <form action="<?= base_url('perfil/guardarDetalles') ?>" method="post" class="form-info">

                    <!-- TAB 0: Datos generales -->
                    <div id="tab0" class="tab-content active">
                        <div class="campo"><label>Nombre:</label><span><?= esc($usuario['nombre']) ?></span></div>
                        <div class="campo"><label>Apellido
                                Paterno:</label><span><?= esc($usuario['apellido_paterno']) ?></span></div>
                        <div class="campo"><label>Apellido
                                Materno:</label><span><?= esc($usuario['apellido_materno']) ?></span></div>
                        <div class="campo"><label>Email:</label><span><?= esc($usuario['email']) ?></span></div>
                        <div class="campo">
                            <label><?= $usuario['matricula'] ? 'Matrícula:' : 'Número de empleado:' ?></label>
                            <span><?= esc($usuario['matricula'] ?? $usuario['num_empleado']) ?></span>
                        </div>
                        <div class="campo"><label>Rol:</label><span><?= esc(session('rol')) ?></span></div>
                    </div>

                    <!-- TAB 1: Datos personales -->
                    <div id="tab1" class="tab-content">
                        <div class="campo">
                            <label>Sexo:</label>
                            <select name="sexo">
                                <option value="">Seleccione</option>
                                <option value="Masculino" <?= ($detalles['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="Femenino" <?= ($detalles['sexo'] ?? '') === 'Femenino' ? 'selected' : '' ?>>
                                    Femenino</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label>Fecha de nacimiento:</label>
                            <input type="date" name="fecha_nacimiento"
                                value="<?= esc($detalles['fecha_nacimiento'] ?? '') ?>">
                        </div>

                        <div class="campo">
                            <label>Estado civil:</label>
                            <select name="estado_civil">
                                <option value="">Seleccione</option>
                                <?php foreach (["Soltero(a)", "Casado(a)", "Unión libre", "Divorciado(a)", "Viudo(a)"] as $ec): ?>
                                    <option value="<?= $ec ?>" <?= ($detalles['estado_civil'] ?? '') === $ec ? 'selected' : '' ?>><?= $ec ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo"><label>CURP:</label><input type="text" name="curp" maxlength="18"
                                value="<?= esc($detalles['curp'] ?? '') ?>"></div>
                        <div class="campo"><label>RFC:</label><input type="text" name="rfc" maxlength="13"
                                value="<?= esc($detalles['rfc'] ?? '') ?>"></div>

                        <div class="campo">
                            <label>País de origen:</label>
                            <select name="pais_origen" data-value="<?= esc($detalles['pais_origen'] ?? '') ?>"></select>
                        </div>
                    </div>

                    <!-- TAB 2: Médicos -->
                    <div id="tab2" class="tab-content">
                        <div class="campo"><label>Peso (kg):</label><input type="number" step="0.01" name="peso"
                                value="<?= esc($detalles['peso'] ?? '') ?>"></div>
                        <div class="campo"><label>Estatura (m):</label><input type="number" step="0.01" name="estatura"
                                value="<?= esc($detalles['estatura'] ?? '') ?>"></div>

                        <div class="campo">
                            <label>Tipo de sangre:</label>
                            <select name="tipo_sangre">
                                <option value="">Seleccione</option>
                                <?php foreach (["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"] as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= ($detalles['tipo_sangre'] ?? '') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label><input type="checkbox" name="antecedente_diabetico"
                                    <?= ($detalles['antecedente_diabetico'] ?? 0) == 1 ? 'checked' : '' ?>> Familiar
                                diabético</label>
                        </div>
                        <div class="campo">
                            <label><input type="checkbox" name="antecedente_hipertenso"
                                    <?= ($detalles['antecedente_hipertenso'] ?? 0) == 1 ? 'checked' : '' ?>> Familiar
                                hipertenso</label>
                        </div>
                        <div class="campo">
                            <label><input type="checkbox" name="antecedente_cardiaco"
                                    <?= ($detalles['antecedente_cardiaco'] ?? 0) == 1 ? 'checked' : '' ?>> Familiar
                                cardiaco</label>
                        </div>
                    </div>

                    <!-- TAB 3: Domicilio -->
                    <div id="tab3" class="tab-content">
                        <div class="campo"><label>Estado:</label><input type="text" name="estado"
                                value="<?= esc($detalles['estado'] ?? '') ?>"></div>
                        <div class="campo"><label>Municipio:</label><input type="text" name="municipio"
                                value="<?= esc($detalles['municipio'] ?? '') ?>"></div>
                        <div class="campo"><label>Colonia:</label><input type="text" name="colonia"
                                value="<?= esc($detalles['colonia'] ?? '') ?>"></div>
                        <div class="campo"><label>Calle:</label><input type="text" name="calle"
                                value="<?= esc($detalles['calle'] ?? '') ?>"></div>
                        <div class="campo"><label>Número exterior:</label><input type="text" name="numero_exterior"
                                value="<?= esc($detalles['numero_exterior'] ?? '') ?>"></div>
                        <div class="campo"><label>Número interior:</label><input type="text" name="numero_interior"
                                value="<?= esc($detalles['numero_interior'] ?? '') ?>"></div>
                    </div>

                    <!-- TAB 4: Comunicación -->
                    <div id="tab4" class="tab-content">
                        <div class="campo"><label>Teléfono:</label><input type="text" name="telefono"
                                value="<?= esc($detalles['telefono'] ?? '') ?>"></div>
                        <div class="campo"><label>Correo alternativo:</label><input type="email"
                                name="correo_alternativo" value="<?= esc($detalles['correo_alternativo'] ?? '') ?>">
                        </div>
                        <div class="campo"><label>Teléfono de trabajo:</label><input type="text" name="telefono_trabajo"
                                value="<?= esc($detalles['telefono_trabajo'] ?? '') ?>"></div>
                    </div>

                    <!-- TAB 5: Formación -->
                    <div id="tab5" class="tab-content">
                        <div class="campo">
                            <label>Grado académico:</label>
                            <select name="grado_academico">
                                <option value="">Seleccione</option>
                                <?php foreach (["Técnico", "Licenciatura", "Ingeniería", "Maestría", "Doctorado", "Posdoctorado"] as $grado): ?>
                                    <option value="<?= $grado ?>" <?= ($detalles['grado_academico'] ?? '') === $grado ? 'selected' : '' ?>><?= $grado ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="campo"><label>Descripción del grado:</label><input type="text"
                                name="descripcion_grado" value="<?= esc($detalles['descripcion_grado'] ?? '') ?>"></div>
                        <div class="campo"><label>Cédula profesional:</label><input type="text"
                                name="cedula_profesional" value="<?= esc($detalles['cedula_profesional'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- TAB 6: Cambiar contraseña -->
                    <div id="tab6" class="tab-content">
                        <h4>Cambiar Contraseña</h4>
                        <div class="campo"><input type="password" name="password" placeholder="Nueva contraseña"></div>
                        <div class="campo"><input type="password" name="confirmar" placeholder="Confirmar contraseña">
                        </div>
                        <button type="submit" formaction="<?= base_url('perfil/actualizarPassword') ?>"
                            class="btn-guardar">Actualizar Contraseña</button>
                    </div>

                    <br>
                    <button type="submit" class="btn-guardar btn-main">Guardar Información</button>
                </form>
            </div>
        </div>
    </main>

    <!-- 📦 Dependencias JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="<?= base_url('assets/js/perfil.js') ?>"></script>
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
</body>

</html>