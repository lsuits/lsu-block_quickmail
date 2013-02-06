function modficar_carpeta(id)
{

	var	boton_guardar="id_Guardar_"+id;
	var boton_editar="id_Modificar_"+id;
	var boton_borrar="id_Borrar_"+id;
	var imput_editar="id_Carpeta_"+id;
	
	
	document.getElementById(imput_editar).readOnly = false; 
	document.getElementById(imput_editar).style.border="inset red 4px";
	document.getElementById(boton_guardar).style.display="inline";
	document.getElementById(boton_editar).style.display="none";
	document.getElementById(boton_borrar).style.display="none";
	
}
function modificar_carpeta(id)
{
	var	boton_guardar="id_Guardar_"+id;
	var boton_editar="id_Modificar_"+id;
	var boton_borrar="id_Borrar_"+id;
	var capa_opercione="capa_operaciones";
	var imput_editar="id_Carpeta_"+id;


	document.getElementById(imput_editar).readOnly = true; 
	document.getElementById(imput_editar).style.border="none";
	var nombre_carpeta=escape(document.getElementById(imput_editar).value);
	document.getElementById(boton_guardar).style.display="none";
	document.getElementById(boton_editar).style.display="inline";
	document.getElementById(boton_borrar).style.display="inline";
	
	runajax(capa_opercione,'funciones.php?rop=guardar_carpeta&nombre_carpeta='+nombre_carpeta+'&id_carpeta='+id,'get','');
	
}
function borrar_carpeta(id, nombre_carpeta,curso)
{
	if (confirm ('Estas seguro de querer borrar La carpeta con todos sus mensajes??: '+transformar_acentos(nombre_carpeta)+' ???')){
	var capa_opercione="capa_operaciones";
	var capa_ocultar="capa_operaciones_"+id;

	document.getElementById(capa_ocultar).style.display="none";
		runajax(capa_opercione,'funciones.php?rop=borrar_carpeta&id_carpeta='+id+'&courseid='+curso,'get','');
		//		runajax('pregunta_93','includes/Formacion_continuada/funciones.php?rop=borrar_pregunta&id_pregunta='+id_pregunta+'&id_categoria='+categoria,'get','');


	}

}
function runajax(objID, serverPage,getOrPost,str) {
		 mostrar_loading(objID);
		//Crear una variable Booleana para comprobar si se está usuando  Internet Explorer.
		var xmlhttp = false;
		//Comprobar si se está usuando  Internet Explorer.
		try {
			//Si la  version  de javascript es superior a 5.
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			//If not, then use the older active x object.
			try {
				//If we are using Internet Explorer.
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (E) {
				//Else we must be using a non-IE browser.
				xmlhttp = false;
			}
		}
		//Si no se está utilizando IE , crear una instancia javascript del objecto.
		if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		var obj = document.getElementById(objID);

		 if (getOrPost=="get"){
			xmlhttp.open("GET", serverPage);
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					if (xmlhttp.responseText.substring(0, 9) == "redirect:") {
						window.location = xmlhttp.responseText.substr(9);
					} else  {
						obj.innerHTML = xmlhttp.responseText;
					}
				}
			}
			xmlhttp.send(null);
		 }else {
			xmlhttp.open("POST", serverPage,true);
  		    xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					if (xmlhttp.responseText.substring(0, 9) == "redirect:") {
						window.location = xmlhttp.responseText.substr(9);
					} else  {
						obj.innerHTML = xmlhttp.responseText;
					}
					
				}else Debug.Trace('Post failed!' + str);
			}
			xmlhttp.send(str);
		 }
		
	}
function mostrar_loading(capa)
  {
	 var cargando="<div class='fondoTransparente'></div> <div class='centrada'><center>	<br /><br /><font color='#0000FF' style='font-weight:bold'> Se paciente se estan procesando los datos</font> <br><img src='http://www.fecyl.com/includes/Formacion_continuada/images/loading.gif' alt='Procensando datos'  title='Procensando datos' border='0'></center></div>";
	document.getElementById(capa).innerHTML =cargando;
}
 function transformar_acentos(cadena)
 {
	 var cad=cadena;
	 cad.replace('á','\u00e1');cad.replace('é','\u00e9');cad.replace('í','\u00ed');cad.replace('ó','\u00f3');cad.replace('ú','\u00fa');cad.replace('Á','\u00c1');cad.replace('É','\u00c9');cad.replace('Í','\u00cd');cad.replace('Ó','\u00d3');	 cad.replace('Ú','\u00da ');cad.replace('ñ','\u00f1  ');cad.replace('Ñ','\u00d1  ');

	 return cad;
	 }

function seleccionar_todo(obj){
	var fobj=document.getElementById(obj);
   for (i=0;i<fobj.elements.length; i++)
        fobj.elements[i].checked=!fobj.elements[i].checked;
} 

function mover_carpetas(usuario, curso,capa, selecion,carpeta_origen){
	var indice=document.getElementById(selecion).selectedIndex;
	var valor = document.getElementById(selecion).options[indice].value;
	// obtenemos los valores de los mensajes seleccionados
	var mensajes_selec=getvalores_mensajes(document.getElementById('formulario_mover_msg'));
	
	runajax(capa,'funciones.php?rop=mover_mensajes&usuario='+usuario+'&curso='+curso+'&carpeta_destino='+valor+'&carpeta_origen='+carpeta_origen+'&mensajes='+mensajes_selec,'get','');

}

function  getvalores_mensajes(fobj){
	 var str;
	 str='';
	for(i = 0; i < fobj.elements.length; i++) {
		if (fobj.elements[i].checked)
		{
			str += fobj.elements[i].value+";";
			document.getElementById('msg___'+fobj.elements[i].value).style.display="none";
		}
	}

		return str;
 }
function buscar_cadena()
{
	/*	var cadena_a_buscar=escape(document.getElementById('buscar_email').value);
		var tipo=document.getElementById('type').value;
		var curso=document.getElementById('courseid').value;
		var userid=document.getElementById('userid').value;
*/
		document.formulario_mover_msg.submit();
		//runajax(capa,'funciones.php?rop=buscar &usuario='+usuario+'&curso='+curso+'&carpeta_destino='+valor+'&carpeta_origen='+carpeta_origen+'&mensajes='+mensajes_selec,'get','');

	}
function submitenter(e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;
	
	if (keycode == 13)
	{
		 buscar_cadena();
		return false;
	}
	else
	return true;
}

