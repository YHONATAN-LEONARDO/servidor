<?php include __DIR__ . '/../../app/config/database.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Filtros de Reportes</title>


</head>
<style>
    /* ----------------- BODY ----------------- */
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #F0F0F0;
        /* Fondo humo */
        color: #111;
        /* Texto principal */
    }

    /* ----------------- BOX PRINCIPAL ----------------- */
    .box {
        max-width: 600px;
        margin: 30px auto;
        background-color: #FFF;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        margin-top: 140px;

    }

    /* ----------------- TITULO ----------------- */
    .box h2 {
        text-align: center;
        color: #E7473C;
        /* Rojo brillante */
        margin-bottom: 20px;
    }

    /* ----------------- FORMULARIO ----------------- */
    form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    form input,
    form select {
        width: 100%;
        padding: 8px 10px;
        margin-bottom: 15px;
        border: 1px solid #CCC;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }

    form input:disabled {
        background-color: #EEE;
        cursor: not-allowed;
    }

    /* ----------------- BOTONES ----------------- */
    button.btn {
        display: inline-block;
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
        text-decoration: none;
        border: none;
        cursor: pointer;
        background-color: #E7473C;
        color: #FFF;
        transition: all 0.2s;
    }

    button.btn:hover {
        background-color: #FFE6E4;
        color: #111;
    }

    /* ----------------- BOTON VOLVER ----------------- */
    .btn-back {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-weight: bold;
        background-color: #111;
        color: #FFF;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background-color: #EEE;
        color: #111;
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <div class="box">
        <h2>Filtros de Reportes</h2>

        <form action="resultado.php" method="get">

            <label>Tipo de reporte</label>
            <select name="tabla" required>
                <option value="ventas">Ventas</option>
                <option value="compras">Compras</option>
            </select>

            <label>Filtro</label>
            <select name="tipo" id="tipoFiltro" required>
                <option value="hoy">Hoy</option>
                <option value="ayer">Ayer</option>
                <option value="mes">Este mes</option>
                <option value="rango">Rango de fechas</option>
            </select>

            <label>Desde</label>
            <input type="date" name="desde" id="desde" disabled>

            <label>Hasta</label>
            <input type="date" name="hasta" id="hasta" disabled>

            <button class="btn" type="submit">Ver Reporte</button>
        </form>

    </div>

    <script>
        const tipo = document.getElementById('tipoFiltro');
        const desde = document.getElementById('desde');
        const hasta = document.getElementById('hasta');

        tipo.addEventListener('change', () => {
            if (tipo.value === 'rango') {
                desde.disabled = false;
                hasta.disabled = false;
            } else {
                desde.disabled = true;
                hasta.disabled = true;
                desde.value = '';
                hasta.value = '';
            }
        });
    </script>

</body>

</html>