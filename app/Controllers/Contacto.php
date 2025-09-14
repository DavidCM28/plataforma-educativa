<?php
namespace App\Controllers;

use App\Models\ContactoModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Contacto extends BaseController
{
    // Para el formulario de la página contacto
    public function guardar()
    {
        return $this->procesarFormulario();
    }

    // Para el formulario rápido del home
    public function enviar()
    {
        return $this->procesarFormulario();
    }

    private function procesarFormulario()
    {
        $rules = [
            'nombre' => 'required|min_length[2]',
            'correo' => 'required|valid_email',
            'mensaje' => 'required|min_length[5]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->with('errors', $this->validator->getErrors())
                             ->withInput();
        }

        $data = [
            'nombre'   => $this->request->getPost('nombre'),
            'correo'   => $this->request->getPost('correo'),
            'telefono' => $this->request->getPost('telefono') ?? null,
            'mensaje'  => $this->request->getPost('mensaje'),
        ];

        // Guardar en la base de datos
        (new ContactoModel())->insert($data);

        // Enviar notificación por correo
        $this->enviarCorreo($data);

        return redirect()->to('/')
                         ->with('mensaje', '✅ Tu mensaje fue enviado y la administración fue notificada.');
    }

    private function enviarCorreo($data)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración servidor Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'davidcm281104@gmail.com'; // 🔴 cambia esto
            $mail->Password   = 'yxyz veai jzac ydjj';     // 🔴 clave de aplicación de Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remitente y destinatario
            $mail->setFrom('davidcm281104@gmail.com', 'Plataforma Educativa');
            $mail->addAddress('bdavid281104@outlook.com', 'Administración'); // 🔴 cambia esto

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = "📩 Nuevo contacto de {$data['nombre']}";
            $mail->Body    = "
                <h3>Nuevo mensaje de contacto</h3>
                <p><strong>Nombre:</strong> {$data['nombre']}</p>
                <p><strong>Correo:</strong> {$data['correo']}</p>
                <p><strong>Teléfono:</strong> {$data['telefono']}</p>
                <p><strong>Mensaje:</strong><br>{$data['mensaje']}</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            log_message('error', "Error enviando correo: {$mail->ErrorInfo}");
        }
    }
}
