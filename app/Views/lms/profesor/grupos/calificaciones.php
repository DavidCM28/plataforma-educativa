<div class="calificaciones-container">

    <h2><i class="fas fa-star-half-alt"></i> Registro de Calificaciones</h2>

    <div id="calificacionesContenido">

        <div id="calificacionesInner">

            <div class="calif-parcial">
                <label for="selectParcial"><i class="fas fa-layer-group"></i> Parcial:</label>
                <select id="selectParcial" class="select-parcial">
                    <option value="1">1° Parcial</option>
                    <option value="2">2° Parcial</option>
                    <option value="3">3° Parcial</option>
                </select>
            </div>

            <div id="mensajeCalificaciones" class="calif-mensaje"></div>

            <div class="tabla-wrapper">
                <table class="tabla-calificaciones">
                    <thead id="calificacionesThead"></thead>
                    <tbody id="calificacionesTbody">
                        <tr>
                            <td colspan="10" class="sin-registros">
                                Selecciona un parcial para cargar las calificaciones…
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <div class="acciones">
        <button id="btnGuardarCalificaciones" class="btn-main">
            <i class="fas fa-save"></i> Guardar calificaciones
        </button>
    </div>

</div>

<script>
    window.CalificacionesUI?.inicializar(<?= $asignacionId ?>);
</script>