/**
 * Weather Widget for Homepage
 * Uses Open-Meteo API (free, no API key required)
 * Location: Borgo Valsugana, Trento
 */

(function() {
    'use strict';
    
    // Coordinates for Borgo Valsugana, Trento
    const LATITUDE = 46.0537;
    const LONGITUDE = 11.4611;
    
    function fetchWeather() {
        const widget = document.getElementById('weather-widget');
        if (!widget) return;
        
        // Open-Meteo API endpoint (free, no key required)
        const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${LATITUDE}&longitude=${LONGITUDE}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,weather_code&timezone=Europe%2FRome&forecast_days=3`;
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                renderWeather(data);
            })
            .catch(error => {
                console.error('Weather fetch error:', error);
                widget.innerHTML = '<p class="weather-error">Impossibile caricare i dati meteo</p>';
            });
    }
    
    function getWeatherIcon(code) {
        // WMO Weather interpretation codes
        const icons = {
            0: '☀️',   // Clear sky
            1: '🌤️',  // Mainly clear
            2: '⛅',   // Partly cloudy
            3: '☁️',   // Overcast
            45: '🌫️', // Fog
            48: '🌫️', // Depositing rime fog
            51: '🌦️', // Drizzle light
            53: '🌦️', // Drizzle moderate
            55: '🌧️', // Drizzle dense
            61: '🌧️', // Rain slight
            63: '🌧️', // Rain moderate
            65: '🌧️', // Rain heavy
            71: '🌨️', // Snow slight
            73: '🌨️', // Snow moderate
            75: '🌨️', // Snow heavy
            77: '❄️',  // Snow grains
            80: '🌦️', // Rain showers slight
            81: '🌧️', // Rain showers moderate
            82: '⛈️',  // Rain showers violent
            85: '🌨️', // Snow showers slight
            86: '🌨️', // Snow showers heavy
            95: '⛈️',  // Thunderstorm
            96: '⛈️',  // Thunderstorm with hail
            99: '⛈️'   // Thunderstorm with heavy hail
        };
        return icons[code] || '🌤️';
    }
    
    function getWeatherDescription(code) {
        const descriptions = {
            0: 'Sereno',
            1: 'Prevalentemente sereno',
            2: 'Parzialmente nuvoloso',
            3: 'Nuvoloso',
            45: 'Nebbia',
            48: 'Nebbia',
            51: 'Pioggerella leggera',
            53: 'Pioggerella moderata',
            55: 'Pioggerella intensa',
            61: 'Pioggia leggera',
            63: 'Pioggia moderata',
            65: 'Pioggia forte',
            71: 'Neve leggera',
            73: 'Neve moderata',
            75: 'Neve forte',
            77: 'Neve',
            80: 'Rovesci leggeri',
            81: 'Rovesci moderati',
            82: 'Rovesci violenti',
            85: 'Nevicate leggere',
            86: 'Nevicate forti',
            95: 'Temporale',
            96: 'Temporale con grandine',
            99: 'Temporale forte con grandine'
        };
        return descriptions[code] || 'Variabile';
    }
    
    function renderWeather(data) {
        const widget = document.getElementById('weather-widget');
        if (!widget) return;
        
        const current = data.current;
        const daily = data.daily;
        
        const currentTemp = Math.round(current.temperature_2m);
        const currentIcon = getWeatherIcon(current.weather_code);
        const currentDesc = getWeatherDescription(current.weather_code);
        const humidity = current.relative_humidity_2m;
        const windSpeed = Math.round(current.wind_speed_10m);
        
        let html = `
            <div class="weather-current">
                <div class="weather-icon">${currentIcon}</div>
                <div class="weather-temp">${currentTemp}°C</div>
                <div class="weather-desc">${currentDesc}</div>
                <div class="weather-details">
                    <span>💧 ${humidity}%</span>
                    <span>💨 ${windSpeed} km/h</span>
                </div>
            </div>
            <div class="weather-forecast">
        `;
        
        // Next 3 days forecast
        for (let i = 1; i < 3; i++) {
            const date = new Date(daily.time[i]);
            const dayName = date.toLocaleDateString('it-IT', { weekday: 'short' });
            const maxTemp = Math.round(daily.temperature_2m_max[i]);
            const minTemp = Math.round(daily.temperature_2m_min[i]);
            const icon = getWeatherIcon(daily.weather_code[i]);
            
            html += `
                <div class="weather-day">
                    <div class="weather-day-name">${dayName}</div>
                    <div class="weather-day-icon">${icon}</div>
                    <div class="weather-day-temp">${maxTemp}° / ${minTemp}°</div>
                </div>
            `;
        }
        
        html += `
            </div>
            <div class="weather-location">Borgo Valsugana, Trento</div>
        `;
        
        widget.innerHTML = html;
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchWeather);
    } else {
        fetchWeather();
    }
    
    // Refresh every 30 minutes
    setInterval(fetchWeather, 30 * 60 * 1000);
})();


