<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDLCMTZ-EATS Web</title>
</head>
<body>


    <div class="header">CDLCMTZ-EATS Web</div>
    <div class="content">
        <div class="card">
            <h1>Hola {{ $detalles['usuario']->username != '' && $detalles['usuario']->username != null ? $detalles['usuario']->username : $detalles['usuario']->nombre }} !!</h1>
            <p>Se solicitó un cambio de contraseña. Por favor, ingresa al siguiente enlace.</p>
            <br>
            <a href="http://localhost:4200/auth/change-password/{{ $detalles['codigo'] }}" class="btn btn-outline-primary">Cambiar contraseña</a>
            <p>Saludos.</p>
            <p>CDLCMTZ-EATS</p>

        </div>
    </div>
    <div class="footer">cdlmtz-eats. 2025 Todos los derechos reservados.</div>

    <style>
        *{
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        }

        /* Table Styles */
        .header, .footer{
            widows: 100%;
            margin: 0;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            background:  rgb(56 189 248 / var(--tw-bg-opacity, 1));
            font-family: Helvetica;
            -webkit-font-smoothing: antialiased;
            padding: 1.75rem;
        }

        .btn {
            --bs-btn-padding-x: 0.75rem;
            --bs-btn-padding-y: 0.375rem;
            --bs-btn-font-family: ;
            --bs-btn-font-size: 1rem;
            --bs-btn-font-weight: 400;
            --bs-btn-line-height: 1.5;
            --bs-btn-color: var(--bs-body-color);
            --bs-btn-bg: transparent;
            --bs-btn-border-width: var(--bs-border-width);
            --bs-btn-border-color: transparent;
            --bs-btn-border-radius: var(--bs-border-radius);
            --bs-btn-hover-border-color: transparent;
            --bs-btn-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(0, 0, 0, 0.075);
            --bs-btn-disabled-opacity: 0.65;
            --bs-btn-focus-box-shadow: 0 0 0 0.25rem rgba(var(--bs-btn-focus-shadow-rgb), .5);
            display: inline-block;
            padding: var(--bs-btn-padding-y) var(--bs-btn-padding-x);
            font-family: var(--bs-btn-font-family);
            font-size: var(--bs-btn-font-size);
            font-weight: var(--bs-btn-font-weight);
            line-height: var(--bs-btn-line-height);
            color: var(--bs-btn-color);
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            border: var(--bs-btn-border-width) solid var(--bs-btn-border-color);
            border-radius: var(--bs-btn-border-radius);
            background-color: var(--bs-btn-bg);
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .btn-outline-primary {
            --bs-btn-color: #0d6efd;
            --bs-btn-border-color: #0d6efd;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #0d6efd;
            --bs-btn-hover-border-color: #0d6efd;
            --bs-btn-focus-shadow-rgb: 13, 110, 253;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #0d6efd;
            --bs-btn-active-border-color: #0d6efd;
            --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
            --bs-btn-disabled-color: #0d6efd;
            --bs-btn-disabled-bg: transparent;
            --bs-btn-disabled-border-color: #0d6efd;
            --bs-gradient: none;
        }

        .content{
            width: 100%;
            background: rgb(241, 241, 241);
            margin: 0;
            padding: .5rem 0;
        }
        .card{
            width: 50%;
            background: white;
            padding: 2rem;
            display: block;
            margin: 1rem auto;
            min-height: 7rem;
            border-radius: 10px;
        }
        .card h1{
            color: rgb(39, 39, 39);
            font-size: 1rem;
            font-weight: 600;
            font-family: Helvetica;
            text-align: left;
            margin: .3rem 0;
        }
        .card p{
            color: rgb(58, 58, 58);
            font-size: .87rem;
            font-family: Helvetica;
            text-align: left;
            margin: .2rem 0;
        }
        .card p span{
            font-size: .91rem;
            font-weight: 600;
        }
        .footer{
            padding: 1.5rem;
            font-weight: 200;
            font-size: .75rem;
        }

        /* Responsive */
        @media (max-width: 767px) {

        }
    </style>
</body>
</html>
