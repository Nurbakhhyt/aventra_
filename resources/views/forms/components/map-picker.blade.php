<div style="margin-bottom: 1rem;">
    <div id="map" style="
        height: 300px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
    "></div>

    <!-- Жасырын input-тар -->
    <input type="hidden" id="lat" name="lat" />
    <input type="hidden" id="lng" name="lng" />
</div>

<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('map').setView([43.238949, 76.889709], 10); // Алматы координатасы

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker;

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(7);
            const lng = e.latlng.lng.toFixed(7);

            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    });
</script>
