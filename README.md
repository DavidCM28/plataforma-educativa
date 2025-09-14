# 📚 Plataforma Educativa

Proyecto en **CodeIgniter 4** para una plataforma educativa que incluye landing page informativa, gestión de usuarios y módulo de contacto con notificaciones vía email.

---

## ⚙️ Requisitos

- [PHP >= 8.1](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/)
- [MySQL](https://dev.mysql.com/downloads/)
- [XAMPP](https://www.apachefriends.org/es/index.html) (opcional, recomendado)
- [Git](https://git-scm.com/)

---

## 🚀 Instalación

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/DavidCM28/plataforma-educativa.git
   cd plataforma-educativa
   ```

2. **Instalar dependencias**

   ```bash
   composer install
   ```

3. **Configurar variables de entorno**

   Copiar el archivo `.env.example` y renombrarlo a `.env`:

   ```bash
   cp .env.example .env
   ```

   Luego, editar con tus credenciales locales o de Railway:

   ```ini
   database.default.hostname = localhost
   database.default.database = plataforma_educativa
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

   ⚠️ **Importante:** nunca subas tus credenciales reales al repositorio.

4. **Crear la base de datos**

   En MySQL crea la BD vacía:

   ```sql
   CREATE DATABASE plataforma_educativa CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```

5. **Ejecutar migraciones**

   ```bash
   php spark migrate
   ```

6. **Levantar el servidor local**

   ```bash
   php spark serve
   ```

   Abre en el navegador: [http://localhost:8080](http://localhost:8080)

---

## 📩 Notificaciones de Contacto

El proyecto usa **PHPMailer** para enviar correos desde los formularios de contacto.

- Configura las credenciales en tu `.env`:

  ```ini
  email.SMTPUser = tu_correo@gmail.com
  email.SMTPPass = tu_password_app
  ```

- ⚠️ Necesitas una **clave de aplicación de Gmail**, no tu contraseña normal.

---

## 🤝 Flujo de trabajo en equipo

1. **Crear rama para tus cambios**

   ```bash
   git checkout -b feature/nueva-funcionalidad
   ```

2. **Subir tus cambios**

   ```bash
   git add .
   git commit -m "Agregada nueva funcionalidad"
   git push origin feature/nueva-funcionalidad
   ```

3. **Abrir un Pull Request** en GitHub para revisión.

---

## 🛠️ Tecnologías

- PHP 8.1
- CodeIgniter 4
- MySQL
- Composer
- PHPMailer
- Bootstrap 5 (frontend)

---

## 👨‍💻 Autores

- Equipo **Ctrl+Shift** – UT Montemorelos
