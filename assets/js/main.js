document.addEventListener('DOMContentLoaded', () => {
    // Theme Switcher Logic
    const themes = ['material', 'flat', 'neumorphism', 'glassmorphism', 'claymorphism'];
    const body = document.body;
    
    // Create Theme Switcher UI
    const switcher = document.createElement('div');
    switcher.className = 'theme-switcher';
    
    themes.forEach(theme => {
        const btn = document.createElement('div');
        btn.className = 'theme-btn';
        btn.title = theme;
        btn.style.backgroundColor = getThemeColor(theme);
        btn.onclick = () => setTheme(theme);
        switcher.appendChild(btn);
    });

    // Dark Mode Toggle
    const darkModeBtn = document.createElement('div');
    darkModeBtn.className = 'dark-mode-toggle';
    darkModeBtn.innerHTML = '🌙';
    darkModeBtn.onclick = toggleDarkMode;
    switcher.appendChild(darkModeBtn);

    document.body.appendChild(switcher);

    // Load saved preferences
    const savedTheme = localStorage.getItem('theme') || 'material';
    const savedDarkMode = localStorage.getItem('darkMode') === 'true';

    setTheme(savedTheme);
    if (savedDarkMode) body.classList.add('dark-mode');

    function setTheme(themeName) {
        body.className = ''; // Reset classes
        body.classList.add(`theme-${themeName}`);
        if (localStorage.getItem('darkMode') === 'true') {
            body.classList.add('dark-mode');
        }
        localStorage.setItem('theme', themeName);
    }

    function toggleDarkMode() {
        body.classList.toggle('dark-mode');
        const isDark = body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark);
        darkModeBtn.innerHTML = isDark ? '☀️' : '🌙';
    }

    function getThemeColor(theme) {
        switch(theme) {
            case 'material': return '#6200ee';
            case 'flat': return '#2c3e50';
            case 'neumorphism': return '#e0e5ec';
            case 'glassmorphism': return '#667eea';
            case 'claymorphism': return '#f0f4f8';
            default: return '#000';
        }
    }
});
