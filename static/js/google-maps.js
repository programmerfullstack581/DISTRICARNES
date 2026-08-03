(function () {
    'use strict';

    var DISTRICARNES_LOCATION = {
        lat: 10.39697399240679,
        lng: -75.55148638476352,
        name: 'DistriCarnes - Hermanos Navarro',
        address: 'Olaya Herrera #34-71A-60, Cartagena de Indias, Colombia',
        phone: '+57 301 5210177',
        hours: 'Lunes a Sábado 8:00–20:00 · Domingo 9:00–17:00'
    };

    var MAP_CONTAINER_ID = 'districarnes-map';
    var FALLBACK_IFRAME_SRC = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3976.6586502248737!2d-75.55148638476352!3d10.39697399240679!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8ef624e4b578f21f%3A0xa6156aaebc72a220!2sDistriCarnes!5e0!3m2!1es-419!2sco!4v1688330717498!5m2!1es-419!2sco';

    var mapInstance = null;
    var markerInstance = null;
    var infoWindowInstance = null;
    var scriptLoaded = false;
    var initQueue = [];
    var isInitializing = false;

    function getApiKey() {
        var cfg = window.DISTRICARNES_CONFIG;
        if (cfg && cfg.googleMapsApiKey) {
            return cfg.googleMapsApiKey;
        }
        if (typeof getConfigValue === 'function') {
            return getConfigValue('GOOGLE_MAPS_API_KEY', '');
        }
        return '';
    }

    function loadGoogleMapsScript(apiKey, callback) {
        if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
            callback();
            return;
        }

        if (typeof google !== 'undefined' && google.maps && !google.maps.Map) {
            var checkInterval = setInterval(function () {
                if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
                    clearInterval(checkInterval);
                    callback();
                }
            }, 200);
            setTimeout(function () {
                clearInterval(checkInterval);
                if (typeof google === 'undefined' || !google.maps || !google.maps.Map) {
                    handleError('Google Maps API no se inicializó a tiempo');
                }
            }, 15000);
            return;
        }

        var scriptId = 'google-maps-api-script';
        if (document.getElementById(scriptId)) {
            var checkReady = setInterval(function () {
                if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
                    clearInterval(checkReady);
                    callback();
                }
            }, 200);
            return;
        }

        var script = document.createElement('script');
        script.id = scriptId;
        script.type = 'text/javascript';
        var apiUrl = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&callback=initDistricarnesMap';
        script.src = apiUrl;
        script.async = true;
        script.defer = true;

        script.onerror = function () {
            handleError('No se pudo cargar el script de Google Maps API');
        };

        window.initDistricarnesMap = function () {
            scriptLoaded = true;
            callback();
        };

        document.head.appendChild(script);
    }

    function buildInfoWindowContent() {
        return '<div id="districarnes-map-info" style="max-width:320px;font-family:Montserrat,Arial,sans-serif;">' +
            '<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">' +
            '<img src="https://maps.google.com/favicon.ico" alt="Mapa" width="24" height="24" style="filter:invert(80%) sepia(94%) saturate(300%) hue-rotate(340deg);"> ' +
            '<span style="font-weight:800;color:#ff0000;font-size:16px;">DistriCarnes</span>' +
            '</div>' +
            '<div style="margin-bottom:6px;font-size:13px;color:#333;">' +
            '<strong style="color:#cc0000;">Hermanos Navarro</strong><br>' +
            DISTRICARNES_LOCATION.address +
            '</div>' +
            '<div style="margin-bottom:6px;font-size:13px;color:#333;">' +
            '⏰ ' + DISTRICARNES_LOCATION.hours +
            '</div>' +
            '<div style="margin-bottom:10px;font-size:13px;color:#333;">' +
            '☎️ <a href="tel:' + DISTRICARNES_LOCATION.phone.replace(/[^\d]/g, '') + '" style="color:#ff0000;text-decoration:none;">' + DISTRICARNES_LOCATION.phone + '</a>' +
            '</div>' +
            '<div style="display:flex;gap:8px;flex-wrap:wrap;">' +
            '<a href="https://www.google.com/maps/dir/?api=1&destination=' + DISTRICARNES_LOCATION.lat + ',' + DISTRICARNES_LOCATION.lng + '" target="_blank" style="background:#ff0000;color:#fff;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">Cómo llegar</a>' +
            '<a href="https://wa.me/573015210177?text=Hola%20DistriCarnes" target="_blank" style="background:#22c55e;color:#fff;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">WhatsApp</a>' +
            '</div>' +
            '</div>';
    }

    function handleError(message) {
        var container = document.getElementById(MAP_CONTAINER_ID);
        if (!container) return;

        container.innerHTML = '';
        container.style.minHeight = '200px';
        container.style.display = 'flex';
        container.style.alignItems = 'center';
        container.style.justifyContent = 'center';
        container.style.background = 'rgba(0,0,0,0.5)';
        container.style.border = '1px dashed rgba(255,255,255,0.2)';
        container.style.borderRadius = '8px';

        var msg = document.createElement('div');
        msg.style.textAlign = 'center';
        msg.style.padding = '20px';
        msg.style.color = '#fff';
        msg.style.maxWidth = '90%';
        msg.innerHTML = '<i class="fas fa-map-marker-alt" style="font-size:2rem;color:#ff0000;margin-bottom:10px;"></i>' +
            '<p style="margin:8px 0;font-size:14px;">Mapa no disponible</p>' +
            '<p style="font-size:12px;color:#888;margin-bottom:12px;">' + message + '</p>' +
            '<a href="https://www.google.com/maps?q=DistriCarnes+Cartagena&hl=es" target="_blank" style="background:#ff0000;color:#fff;padding:8px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:12px;">Ver en Google Maps</a>';
        container.appendChild(msg);
    }

    function loadFallbackIframe() {
        var container = document.getElementById(MAP_CONTAINER_ID);
        if (!container) return;

        container.innerHTML = '';

        var iframe = document.createElement('iframe');
        iframe.src = FALLBACK_IFRAME_SRC;
        iframe.width = '100%';
        iframe.height = '350';
        iframe.style.border = '0';
        iframe.style.borderRadius = '8px';
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
        iframe.setAttribute('aria-label', 'Ubicación de DistriCarnes en Cartagena, Colombia');
        iframe.setAttribute('title', 'Mapa de localización de DistriCarnes');
        iframe.allowFullscreen = true;

        container.appendChild(iframe);
    }

    function initMap() {
        var container = document.getElementById(MAP_CONTAINER_ID);
        if (!container) return;

        var lat = parseFloat(container.getAttribute('data-lat')) || DISTRICARNES_LOCATION.lat;
        var lng = parseFloat(container.getAttribute('data-lng')) || DISTRICARNES_LOCATION.lng;

        var mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 16,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            backgroundColor: '#000000',
            styles: [
                { elementType: 'labels.text.fill', stylers: [{ color: '#ffffff' }] },
                { elementType: 'labels.text.stroke', stylers: [{ color: '#000000' }] },
                { elementType: 'labels.icon', stylers: [{ visibility: 'simplified' }] },
                {
                    featureType: 'road',
                    elementType: 'geometry',
                    stylers: [{ color: '#1a1a1a' }]
                },
                {
                    featureType: 'water',
                    elementType: 'geometry',
                    stylers: [{ color: '#0b0b0b' }]
                },
                {
                    featureType: 'landscape',
                    elementType: 'geometry',
                    stylers: [{ color: '#000000' }]
                },
                {
                    featureType: 'poi',
                    elementType: 'geometry',
                    stylers: [{ color: '#000000' }]
                },
                {
                    featureType: 'administrative',
                    elementType: 'labels',
                    stylers: [{ visibility: 'simplified' }]
                },
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ],
            streetViewControl: true,
            streetViewControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
            fullscreenControl: true,
            fullscreenControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
            zoomControl: true,
            zoomControlOptions: { position: google.maps.ControlPosition.LEFT_BOTTOM },
            mapTypeControl: false,
            rotateControl: false,
            panControl: true,
            tilt: 0,
            gestureHandling: 'cooperative'
        };

        try {
            mapInstance = new google.maps.Map(container, mapOptions);
        } catch (e) {
            console.error('[DistriCarnes Maps] Error al crear el mapa:', e);
            handleError('Error al inicializar el mapa');
            return;
        }

        var markerPosition = { lat: lat, lng: lng };

        var svgMarker = {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#ff0000',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            strokeOpacity: 1,
            scale: 10
        };

        try {
            markerInstance = new google.maps.Marker({
                position: markerPosition,
                map: mapInstance,
                title: DISTRICARNES_LOCATION.name,
                icon: svgMarker,
                animation: google.maps.Animation.DROP
            });
        } catch (e) {
            console.error('[DistriCarnes Maps] Error al crear el marcador:', e);
        }

        infoWindowInstance = new google.maps.InfoWindow({
            content: buildInfoWindowContent(),
            maxWidth: 360,
            pixelOffset: new google.maps.Size(0, -40)
        });

        if (markerInstance) {
            google.maps.event.addListener(markerInstance, 'click', function () {
                if (infoWindowInstance) {
                    infoWindowInstance.open(mapInstance, markerInstance);
                }
            });

            google.maps.event.addListener(mapInstance, 'idle', function () {
                if (infoWindowInstance && typeof infoWindowInstance.getMap === 'function') {
                    if (!infoWindowInstance.getMap()) {
                        infoWindowInstance.open(mapInstance, markerInstance);
                    }
                }
            });

            if (infoWindowInstance) {
                infoWindowInstance.open(mapInstance, markerInstance);
            }
        }

        google.maps.event.addListenerOnce(mapInstance, 'idle', function () {
            google.maps.event.trigger(mapInstance, 'resize');
        });
    }

    function initMapWithRetry(attempts) {
        attempts = attempts || 0;
        var maxAttempts = 3;

        function attempt() {
            if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
                initMap();
                return;
            }

            if (attempts < maxAttempts) {
                attempts++;
                setTimeout(attempt, 500);
            } else {
                console.error('[DistriCarnes Maps] Google Maps API no disponible después de ' + maxAttempts + ' intentos');
                loadFallbackIframe();
            }
        }

        attempt();
    }

    function bootstrap() {
        var container = document.getElementById(MAP_CONTAINER_ID);
        if (!container) {
            return;
        }

        var apiKey = getApiKey();

        if (!apiKey) {
            console.warn('[DistriCarnes Maps] No se configuró GOOGLE_MAPS_API_KEY. Cargando mapa estático como fallback.');
            loadFallbackIframe();
            return;
        }

        if (isInitializing) {
            initQueue.push(function () { bootstrap(); });
            return;
        }
        isInitializing = true;

        loadGoogleMapsScript(apiKey, function () {
            isInitializing = false;
            initMapWithRetry();

            while (initQueue.length > 0) {
                var fn = initQueue.shift();
                setTimeout(fn, 0);
            }
        });
    }

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    onReady(bootstrap);

    window.DistriCarnesMap = {
        init: bootstrap,
        reload: function () {
            if (mapInstance) {
                google.maps.event.clearInstanceListeners(mapInstance);
                mapInstance = null;
            }
            if (markerInstance) {
                markerInstance.setMap(null);
                markerInstance = null;
            }
            if (infoWindowInstance) {
                infoWindowInstance.close();
                infoWindowInstance = null;
            }
            scriptLoaded = false;
            bootstrap();
        },
        getMap: function () { return mapInstance; },
        getMarker: function () { return markerInstance; }
    };
})();
