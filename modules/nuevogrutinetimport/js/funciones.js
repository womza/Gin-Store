
$(function(){if(activarAjax==1){setInterval(function(){actualizar();},tiempoAjax*1000);}
$('dl.acordeonInterno > dd').not('dt.activo + dd').hide();$('dl.acordeonInterno > dt').click(function(){if($(this).hasClass('activo')){$(this).removeClass('activo');$(this).next().slideUp();}else{$('dl.acordeonInterno dt').removeClass('activo');$(this).addClass('activo');$('dl.acordeonInterno dd').slideUp();$(this).next().slideDown();}});$('dl.acordeon > dd').not('dt.activo + dd').hide();$('dl.acordeon > dt').click(function(){if($(this).hasClass('activo')){$(this).removeClass('activo');$(this).next().slideUp();}else{$('dl dt').removeClass('activo');$(this).addClass('activo');$('dl dd').slideUp();$(this).next().slideDown();}});$('select[name^="tienda"]').change(function(){var idTienda=$(this).val();var catalogo=/\[(\d+)\]/.exec($(this).attr('name'));if(idTienda==-1){$(this).parent().parent().find('.categoriaPadre').html('');}
else{obtenerCategorias(idTienda,catalogo[1],$(this).parent().parent());}});$('select[name^="tienda"]').change();});function obtenerCategorias(idTienda,catalogo,tr){$.getJSON(baseUrl+'nuevoGrutinetImportAjax.php?callback=?',{accion:'obtenerCategoriasTienda',idTienda:idTienda,catalogo:catalogo},function(respuesta){if(respuesta.ok){var select=$('<select name="idCategoriaPadre['+catalogo+']"></select>');for(var x=0,len=respuesta.datos.length;x<len;x++){var espacios='';for(var y=0;y<respuesta.datos[x].profundidad;y++){espacios+='&nbsp;&nbsp;';}
select.append('<option '+(respuesta.datos[x].seleccionado?'selected="selected"':'')+' value="'+respuesta.datos[x].idCategoria+'">'+espacios+respuesta.datos[x].nombre+'</option>');}
tr.find('.categoriaPadre').html(select);}});}
function actualizar(){$.getJSON(url+'refrescoEstadisticasNuevoGrutinet.php?callback=?',function(respuesta){var currentdate=new Date();var datetimeSync="<b>Ultima Sync: "+currentdate.getDate()+"/"
+(currentdate.getMonth()+1)+"/"
+currentdate.getFullYear()+" @ "
+currentdate.getHours()+":"
+currentdate.getMinutes()+":"
+currentdate.getSeconds()+"</b>";$('#menuTab6Sheet').html(respuesta.ultimosProductos+'<br/>'+respuesta.estadisticas);showSuccessMessage(datetimeSync,tiempoMsgAjax*1000);});}