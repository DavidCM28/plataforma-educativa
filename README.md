# 📚 Plataforma Educativa – Guía Completa de Instalación y Desarrollo

Este documento sirve como **manual para desarrolladores** del proyecto **Plataforma Educativa** en **CodeIgniter 4**.  
Aquí encontrarás desde la configuración inicial hasta el flujo de trabajo para frontend y backend.

---

## ✅ Requisitos Previos

Antes de empezar, asegúrate de tener instalado:

- [PHP >= 8.1](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/) (gestor de dependencias PHP)
- [MySQL](https://dev.mysql.com/downloads/) o [MariaDB](https://mariadb.org/)
- [XAMPP](https://www.apachefriends.org/es/index.html) (opcional, recomendado para entorno local)
- [Git](https://git-scm.com/) (control de versiones)
- [Visual Studio Code](https://code.visualstudio.com/) (editor recomendado)

Extensiones útiles en VSCode:

- **PHP Intelephense**
- **MySQL**
- **GitHub Pull Requests**
- **PHP Server**

---

## ⚙️ Instalación de dependencias

### 1. Verificar que PHP y Composer estén instalados

```bash
php -v
composer -V
```

Si no los tienes, instala desde sus páginas oficiales.

---

## 📂 Crear el proyecto

### 1. Crear un nuevo proyecto CodeIgniter 4

```bash
composer create-project codeigniter4/appstarter plataforma-educativa
cd plataforma-educativa
```

Esto genera la carpeta con la estructura base del framework.

---

## 🔧 Configuración inicial de Git

### 1. Configurar Git en tu PC

```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tuemail@ejemplo.com"
```

### 2. Inicializar Git en la carpeta del proyecto

```bash
git init
```

### 3. Conectar con el repositorio remoto en GitHub

```bash
git remote add origin https://github.com/TU_USUARIO/plataforma-educativa.git
git branch -M main
git push -u origin main
```

---

## 🔄 Clonar el repositorio en otra computadora

Si ya existe el repo en GitHub:

```bash
git clone https://github.com/TU_USUARIO/plataforma-educativa.git
cd plataforma-educativa
composer install
```

---

## 📌 Uso de Git en el flujo de trabajo

### Crear rama nueva para tus cambios

```bash
git checkout -b feature/nueva-funcionalidad
```

### Guardar y subir cambios

```bash
git add .
git commit -m "Descripción de cambios"
git push origin feature/nueva-funcionalidad
```

### Descargar cambios del equipo

```bash
git pull origin main
```

---

## 🚀 Iniciar el servidor local

Desde la carpeta del proyecto:

```bash
php spark serve
```

Abre en navegador:  
👉 [http://localhost:8080](http://localhost:8080)

---

## 🎨 Flujo Frontend (Vistas)

- Las vistas están en `/app/Views/`.
- Usa `layouts/header.php` y `layouts/footer.php` para mantener consistencia.
- Las vistas dinámicas reciben datos desde los controladores.

Ejemplo de vista con becas dinámicas:

```php
<?php foreach ($becas as $beca): ?>
<div class="card">
  <h3><?= esc($beca['nombre']) ?></h3>
  <p><?= esc($beca['descripcion']) ?></p>
</div>
<?php endforeach; ?>
```

Para levantar la vista en navegador, define una ruta en `/app/Config/Routes.php`:

```php
$routes->get('/becas', 'Home::becas');
```

Y en el controlador `Home.php`:

```php
public function becas()
{
    return view('becas', ['title' => 'Becas']);
}
```

---

## ⚙️ Flujo Backend (Migraciones, Seeders, Modelos y Controladores)

### 📂 Migraciones

Las migraciones permiten **crear y versionar tablas** de la BD desde PHP.

Crear una migración:

```bash
php spark make:migration CreateBecasTable
```

Ejemplo dentro de la migración:

```php
public function up()
{
    $this->forge->addField([
        'id' => ['type' => 'INT', 'auto_increment' => true],
        'nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
        'descripcion' => ['type' => 'TEXT'],
        'porcentaje' => ['type' => 'INT'],
        'requisitos' => ['type' => 'TEXT'],
        'servicio_becario_horas' => ['type' => 'INT', 'null' => true],
        'created_at' => ['type' => 'DATETIME', 'null' => true],
        'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->createTable('becas');
}
```

Ejecutar migraciones:

```bash
php spark migrate
```

---

### 📂 Seeders

Los seeders permiten **llenar tablas con datos iniciales**.

Crear un seeder:

```bash
php spark make:seeder BecaSeeder
```

Ejemplo:

```php
public function run()
{
    $data = [
        [
            'nombre' => 'Beca de Empleado',
            'descripcion' => 'Cubre el 100% de colegiatura',
            'porcentaje' => 100,
            'requisitos' => 'Promedio mínimo 7.0, sin adeudos',
            'servicio_becario_horas' => 0,
        ],
        [
            'nombre' => 'Beca de Excelencia',
            'descripcion' => 'Apoyo al mérito académico',
            'porcentaje' => 100,
            'requisitos' => 'Promedio ≥ 9.5, sin adeudos',
            'servicio_becario_horas' => 60,
        ]
    ];
    $this->db->table('becas')->insertBatch($data);
}
```

Ejecutar seeder:

```bash
php spark db:seed BecaSeeder
```

---

### 📂 Modelos

Los modelos representan tablas de la base de datos.

```bash
php spark make:model BecaModel
```

Ejemplo:

```php
namespace App\Models;
use CodeIgniter\Model;

class BecaModel extends Model {
    protected $table = 'becas';
    protected $allowedFields = [
        'nombre', 'descripcion', 'porcentaje',
        'requisitos', 'servicio_becario_horas'
    ];
}
```

---

### 📂 Controladores

Los controladores reciben peticiones y llaman a los modelos/vistas.

Ejemplo en `Home.php`:

```php
use App\Models\BecaModel;

public function index()
{
    $becaModel = new BecaModel();
    $becas = $becaModel->findAll();

    return view('home', [
        'title' => 'Inicio',
        'becas' => $becas
    ]);
}
```

---

## 🔑 Archivo `.env`

El archivo `.env` guarda configuraciones **locales** (no se sube a Git porque está en `.gitignore`).

Ejemplo:

```ini
app.baseURL = 'http://localhost:8080'

database.default.hostname = localhost
database.default.database = plataforma_educativa
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Cada desarrollador debe crear el suyo según su entorno.

---

## 🛠️ Flujo de trabajo recomendado

1. Clonar el repositorio.
2. Configurar `.env`.
3. Crear la base de datos vacía.
4. Ejecutar `composer install`.
5. Aplicar migraciones y seeders.
6. Levantar servidor con `php spark serve`.
7. Los frontend trabajan en `/app/Views/`.
8. Los backend crean modelos, controladores, rutas y migraciones.
9. Subir cambios a una rama propia y hacer Pull Request.
