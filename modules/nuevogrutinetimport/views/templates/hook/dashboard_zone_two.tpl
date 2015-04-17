<section id="dashnuevogrutinet" class="panel widget allow-push">
	<div class="panel-heading">
            <i class="icon-bar-chart"></i> <a href='{$urlModulo}'>{l s='Actividad Nuevo Grutinet Import' mod='nuevoGrutinetImport'}</a>
        </div>
        <ul class="data_list">
            <li>
                <span class="data_label size_l">{l s='Nº veces procesado' mod='nuevoGrutinetImport'}</span>
                <p class="data_value size_l">{$datosEstadisticos['archivoProcesado']}</p>
            </li>
            <li>
                <span class="data_label size_l">{l s='Procesando actualmente' mod='nuevoGrutinetImport'}</span>
                <span class="data_value size_l">{$datosEstadisticos['textoNivelProcesado']}</span>
            </li>

            <li>
                    <span class="data_label">{l s='Productos procesados' mod='nuevoGrutinetImport'}</span>
                    <span class="data_value size_md">
                            <span>{$datosEstadisticos['productosProcesados']}</span>
                    </span>
            </li>
            <li>
                    <span class="data_label">{l s='Atributos procesados' mod='nuevoGrutinetImport'}</span>
                    <span class="data_value size_md">
                            <span>{$datosEstadisticos['atributosProcesados']}</span>
                    </span>
            </li>
            <li>
                    <span class="data_label">{l s='Categorias procesadas' mod='nuevoGrutinetImport'}</span>
                    <span class="data_value size_md">
                            <span>{$datosEstadisticos['categoriasProcesadas']}</span>
                    </span>
            </li>
            <li>
                    <span class="data_label">{l s='Imagenes pendientes' mod='nuevoGrutinetImport'}</span>
                    <span class="data_value size_md">
                            <span>{$datosEstadisticos['imagenesPendientes']}</span>
                    </span>
            </li>
        </ul>
        <div class="table-responsive">
            <table class="table data_table">
                <thead>
                    <tr>
                        <th>{l s='Archivo' mod='nuevoGrutinetImport'}</th>
                        <th>{l s='Estado' mod='nuevoGrutinetImport'}</th>
                        <th>{l s='Fecha Ultima actualizacion' mod='nuevoGrutinetImport'}</th>
                        <th>{l s='Fecha Descarga' mod='nuevoGrutinetImport'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$datosEstadisticos['result_query'] item=elemento}
                        <tr>
                            <td>{$elemento['id_imax_nuevo_grutinet_archivos']}</td>
                            <td><img src='{$elemento['iconoProceso']}' alt='{$elemento['iconoAlt']}' title='{$elemento['iconoTitle']}'/></td>
                            <td>{$elemento['Fecha']}</td>
                            <td>{$elemento['Descarga']}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
</section>
<script>
    $( document ).ready(function() {
       $('#dashnuevogrutinet').removeClass('loading');
     });
    </script>