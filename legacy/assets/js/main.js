// Theme Switcher Logic
const themes = ['material', 'flat', 'neumorphism', 'glassmorphism', 'claymorphism'];

// Define functions globally
function setTheme(themeName) {
    const body = document.body;
    body.className = ''; // Reset classes
    body.classList.add(`theme-${themeName}`);
    if (localStorage.getItem('darkMode') === 'true') {
        body.classList.add('dark-mode');
    }
    localStorage.setItem('theme', themeName);
}

function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    updateDarkModeIcon();
}

function updateDarkModeIcon() {
    const body = document.body;
    const icon = document.getElementById('darkModeIcon');
    if (icon) {
        icon.innerHTML = body.classList.contains('dark-mode') ? '☀️' : '🌙';
    }
}

function getThemeColor(theme) {
    switch (theme) {
        case 'material': return '#6200ee';
        case 'flat': return '#2c3e50';
        case 'neumorphism': return '#e0e5ec';
        case 'glassmorphism': return '#667eea';
        case 'claymorphism': return '#f0f4f8';
        default: return '#000';
    }
}

function toggleView(viewType) {
    const container = document.getElementById('monitoring-container');
    if (container) {
        container.className = `view-mode-${viewType}`;
        localStorage.setItem('viewMode', viewType);

        // Update buttons
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`.view-btn[onclick="toggleView('${viewType}')"]`);
        if (activeBtn) activeBtn.classList.add('active');
    }
}

// Expose toggleView
window.toggleView = toggleView;

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    // Create Theme Switcher UI
    const switcher = document.createElement('div');
    switcher.className = 'theme-switcher collapsed';

    const optionsContainer = document.createElement('div');
    optionsContainer.className = 'theme-options';

    themes.forEach(theme => {
        const btn = document.createElement('div');
        btn.className = 'theme-btn';
        btn.title = theme;
        btn.style.backgroundColor = getThemeColor(theme);
        btn.onclick = () => setTheme(theme);
        optionsContainer.appendChild(btn);
    });

    // Dark Mode Toggle
    const darkModeBtn = document.createElement('div');
    darkModeBtn.className = 'dark-mode-toggle';
    darkModeBtn.innerHTML = '🌙';
    darkModeBtn.onclick = toggleDarkMode;
    optionsContainer.appendChild(darkModeBtn);

    switcher.appendChild(optionsContainer);

    // Toggle Button
    const toggleBtn = document.createElement('div');
    toggleBtn.className = 'theme-toggle-btn';
    toggleBtn.innerHTML = '🎨';
    toggleBtn.onclick = () => {
        switcher.classList.toggle('collapsed');
    };
    switcher.appendChild(toggleBtn);

    document.body.appendChild(switcher);

    // Load saved preferences
    const savedTheme = localStorage.getItem('theme') || 'material';
    const savedDarkMode = localStorage.getItem('darkMode') === 'true';

    setTheme(savedTheme);
    if (savedDarkMode) {
        // Ensure dark mode class is added if setTheme didn't add it (though setTheme does check it)
        // But setTheme adds it based on localStorage, so it should be fine.
        // Let's just make sure the icon is updated.
    }
    updateDarkModeIcon();
});
