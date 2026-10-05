/*====================ENCABEZADO====================
ARCHIVO: Publico/Recursos/js/gestorPlugins.js
==================================================
=====================DETALLES=====================
QUÉ HACE: administra la carga de CSS/JS con fallback
    CDN -> local -> CDN y expone alListo/reportarFallos.
VINCULADO A: lo cargan encabezadoAdmin.php (CSS) y
    pieAdmin.php (cadena de scripts e inicialización).
SI SE ALTERA: si cambia el formato de data-pasos o las
    funciones públicas, actualizar ambos incluidos.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================
*/
(function () {
    'use strict';

    // Buffer de recursos que agotaron sus pasos (se vacía al reportar)
    window.fallosCargaPlugins = [];
    var resolutoresDeScript = {};
    var callbacksListo = [];
    var cadenaTerminada = false;

    /*====================ENCABEZADO====================
    FUNCIÓN: aplicarPaso() | ROL: gestor (JS)
    ==================================================
    =====================DETALLES=====================
    QUÉ HACE: apunta un link/script al CDN o al local.
    VINCULADO A: lo usa el manejador de errores de carga.
    SI SE ALTERA: si cambian los valores de data-pasos,
        revisar también el manejador que lo invoca.
    FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
    ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
    ==================================================
    */
    function aplicarPaso(elemento, paso) {
        var url = paso === 'local'
            ? elemento.getAttribute('data-local')
            : elemento.getAttribute('data-cdn');
        if (!url) { return; }
        if (elemento.tagName === 'LINK') {
            // En <link> mutar href SÍ reanuda la carga (probado en Chromium)
            elemento.href = url;
            return;
        }
        // En <script> mutar src NO reanuda la carga: hay que crear un nodo
        // nuevo con el paso siguiente, en la misma posición, y soltar el viejo
        var nuevo = document.createElement('script');
        ['data-nombre', 'data-pasos', 'data-local', 'data-cdn'].forEach(function (atributo) {
            var valor = elemento.getAttribute(atributo);
            if (valor !== null) { nuevo.setAttribute(atributo, valor); }
        });
        nuevo.setAttribute('data-paso-actual', elemento.getAttribute('data-paso-actual'));
        nuevo.async = false;
        // La clave del resolutor es data-nombre (la usa cargarScript)
        var clave = elemento.getAttribute('data-nombre');
        if (clave && resolutoresDeScript[clave]) {
            nuevo.onload = function () { resolutoresDeScript[clave](true); };
        }
        nuevo.src = url;
        elemento.parentNode.insertBefore(nuevo, elemento);
        elemento.parentNode.removeChild(elemento);
    }

    // Captura global de errores de recursos (error no burbujea)
    document.addEventListener('error', function (evento) {
        var elemento = evento.target;
        if (!elemento || !elemento.getAttribute) { return; }
        var textoPasos = elemento.getAttribute('data-pasos');
        if (!textoPasos) { return; } // recurso no gestionado: se ignora

        var pasos = textoPasos.split(',');
        var actual = parseInt(elemento.getAttribute('data-paso-actual') || '0', 10);
        var siguiente = actual + 1;

        if (siguiente < pasos.length) {
            elemento.setAttribute('data-paso-actual', String(siguiente));
            aplicarPaso(elemento, pasos[siguiente]);
            return;
        }

        // Sin pasos restantes: fallo total del recurso
        var nombre = elemento.getAttribute('data-nombre') || '(recurso sin nombre)';
        window.fallosCargaPlugins.push({
            nombre: nombre,
            secuencia: pasos.join(' -> '),
            rutaLocal: elemento.getAttribute('data-local') || '(sin ruta local)'
        });
        if (elemento.tagName === 'SCRIPT' && resolutoresDeScript[nombre]) {
            resolutoresDeScript[nombre](false);
        }
    }, true);

    function ejecutarCallbacksListo() {
        var cola = callbacksListo.splice(0);
        cola.forEach(function (funcion) {
            try {
                funcion();
            } catch (error) {
                console.error('[gestorPlugins] error en callback alListo:', error);
            }
        });
    }

    function intentarEjecutarListo() {
        if (cadenaTerminada && document.readyState !== 'loading') {
            ejecutarCallbacksListo();
        }
    }

    document.addEventListener('DOMContentLoaded', intentarEjecutarListo);

    window.gestorPlugins = {

        /*====================ENCABEZADO====================
        FUNCIÓN: alListo() | ROL: gestor (API pública)
        ==================================================
        =====================DETALLES=====================
        QUÉ HACE: ejecuta la función cuando la cadena de
            scripts terminó y el DOM está listo.
        VINCULADO A: lo usan pieAdmin.php y Panel/index.php
            (reemplaza a DOMContentLoaded para las libs).
        SI SE ALTERA: si cambia el criterio de "listo",
            revisar los callbacks que dependen de él.
        FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
        ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
        ==================================================
        */
        alListo: function (funcion) {
            callbacksListo.push(funcion);
            intentarEjecutarListo();
        },

        /*====================ENCABEZADO====================
        FUNCIÓN: cargarScript() | ROL: gestor (API pública)
        ==================================================
        =====================DETALLES=====================
        QUÉ HACE: crea un script que sigue la secuencia de
            data-pasos y resuelve al cargar o al agotar pasos.
        VINCULADO A: lo invoca cargarNiveles() del mismo gestor.
        SI SE ALTERA: si cambia el nombre de los data-atributos,
            actualizar también el manejador de errores global.
        FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
        ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
        ==================================================
        */
        cargarScript: function (definicion) {
            return new Promise(function (resolver) {
                var etiqueta = document.createElement('script');
                etiqueta.setAttribute('data-nombre', definicion.nombre);
                etiqueta.setAttribute('data-pasos', definicion.pasos);
                etiqueta.setAttribute('data-paso-actual', '0');
                if (definicion.local) { etiqueta.setAttribute('data-local', definicion.local); }
                if (definicion.cdn) { etiqueta.setAttribute('data-cdn', definicion.cdn); }
                etiqueta.async = false;
                resolutoresDeScript[definicion.nombre] = resolver;
                etiqueta.onload = function () { resolver(true); };
                var primerPaso = definicion.pasos.split(',')[0];
                etiqueta.src = primerPaso === 'local' ? definicion.local : definicion.cdn;
                document.head.appendChild(etiqueta);
            });
        },

        /*====================ENCABEZADO====================
        FUNCIÓN: cargarNiveles() | ROL: gestor (API pública)
        ==================================================
        =====================DETALLES=====================
        QUÉ HACE: carga scripts nivel por nivel (paralelo
            dentro de cada nivel) y marca la cadena como
            terminada sin abortar por un fallo aislado.
        VINCULADO A: la arma pieAdmin.php con el orden de
            dependencias (jQuery antes que Select2/DataTables).
        SI SE ALTERA: si cambia el orden de niveles, revisar
            los inits del pie que dependen de esas libs.
        FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
        ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
        ==================================================
        */
        cargarNiveles: function (niveles) {
            var promesa = Promise.resolve();
            niveles.forEach(function (nivel) {
                promesa = promesa.then(function () {
                    return Promise.all(nivel.map(window.gestorPlugins.cargarScript));
                });
            });
            return promesa.then(function () {
                cadenaTerminada = true;
                intentarEjecutarListo();
            });
        },

        /*====================ENCABEZADO====================
        FUNCIÓN: reportarFallos() | ROL: gestor (API pública)
        ==================================================
        =====================DETALLES=====================
        QUÉ HACE: emite una sola alerta de Alertify con cada
            recurso agotado; si Alertify falta, usa alert().
        VINCULADO A: la llama pieAdmin.php tras la cadena.
        SI SE ALTERA: si cambia el formato de la alerta,
            revisar el texto esperado en las pruebas E2E.
        FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
        ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
        ==================================================
        */
        reportarFallos: function () {
            var fallos = window.fallosCargaPlugins;
            if (!fallos.length) { return; }
            var detalle = fallos.map(function (fallo) {
                return '• ' + fallo.nombre + ' — probado: ' + fallo.secuencia +
                    '\n  copia local: ' + fallo.rutaLocal;
            }).join('\n');
            var texto = 'No se pudieron cargar ' + fallos.length + ' recurso(s):\n\n' +
                detalle + '\n\nVerificá tu conexión a internet o los archivos de Publico/Recursos/.';
            if (window.alertify && typeof window.alertify.alert === 'function') {
                window.alertify.alert('Problemas al cargar recursos', texto);
            } else {
                window.alert('Problemas al cargar recursos\n\n' + texto);
            }
        }
    };
})();
