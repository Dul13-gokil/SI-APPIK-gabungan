{{--
    Lapisan jaringan jalan OpenStreetMap + perkiraan kewenangan.
    Dipakai bersama oleh portal Operator dan Petugas.
    Klasifikasi berasal dari kelas jalan OSM (lihat RoadAuthorityClassifier), BUKAN
    penetapan resmi Dinas; koreksi manual ada di database/data/road_authority_overrides.json.
--}}
<script>
    function attachRoadsLayer(map) {
        if (!map || map._roadsLayerAttached) return;
        map._roadsLayerAttached = true;

        // Pane khusus agar garis jalan tidak menutupi marker aduan.
        if (!map.getPane('roadsPane')) {
            const pane = map.createPane('roadsPane');
            pane.style.zIndex = 350;
        }

        const styles = {
            NASIONAL:        { color: '#DC2626', weight: 4 },
            PROVINSI:        { color: '#D97706', weight: 3.5 },
            KABUPATEN:       { color: '#2563EB', weight: 3 },
            TIDAK_DIKETAHUI: { color: '#64748B', weight: 2, dashArray: '5,5' }
        };
        const labels = {
            NASIONAL: 'Jalan Nasional',
            PROVINSI: 'Jalan Provinsi',
            KABUPATEN: 'Jalan Kabupaten',
            TIDAK_DIKETAHUI: 'Kewenangan belum ditetapkan'
        };

        fetch('{{ asset('geojson/roads_kolaka_utara.geojson') }}')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(geojson => {
                const groups = {};

                (geojson.features || []).forEach(feature => {
                    const authority = (feature.properties && feature.properties.authority) || 'TIDAK_DIKETAHUI';
                    if (!groups[authority]) {
                        groups[authority] = L.layerGroup();
                    }

                    L.geoJSON(feature, {
                        pane: 'roadsPane',
                        style: Object.assign({ fill: false }, styles[authority] || styles.TIDAK_DIKETAHUI),
                        onEachFeature: (feat, layer) => {
                            const p = feat.properties || {};
                            const name = p.name || 'Ruas tanpa nama';
                            layer.bindPopup(
                                '<div style="font-family:Inter,sans-serif;font-size:12px;">'
                                + '<b>' + name + '</b><br>'
                                + 'Kewenangan (perkiraan OSM): ' + (labels[p.authority] || p.authority) + '<br>'
                                + 'Kelas jalan: ' + (p.highway || '-') + '</div>'
                            );
                        }
                    }).addTo(groups[authority]);
                });

                const overlays = {};
                Object.keys(groups).forEach(authority => {
                    const label = labels[authority] || authority;
                    overlays[label] = groups[authority];
                    groups[authority].addTo(map);
                });

                L.control.layers(null, overlays, { position: 'topright', collapsed: false }).addTo(map);
            })
            .catch(err => console.warn('Gagal memuat jaringan jalan OSM:', err));
    }
</script>
