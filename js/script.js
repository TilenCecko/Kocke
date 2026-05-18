const canvas = document.getElementById('fireworks');
const ctx = canvas.getContext('2d');

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let fireworks = [];

class Firework {
    constructor(x, y) {
        this.x = x;
        this.y = y;
        this.particles = [];

        for (let i = 0; i < 50; i++) {
            this.particles.push({
                x: this.x,
                y: this.y,
                radius: Math.random() * 3 + 1,
                color: `hsl(${Math.random() * 360}, 100%, 60%)`,
                speedX: (Math.random() - 0.5) * 8,
                speedY: (Math.random() - 0.5) * 8,
                alpha: 1
            });
        }
    }

    update() {
        this.particles.forEach(p => {
            p.x += p.speedX;
            p.y += p.speedY;
            p.alpha -= 0.015;
        });
    }

    draw() {
        this.particles.forEach(p => {
            ctx.globalAlpha = p.alpha;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.fill();
        });

        ctx.globalAlpha = 1;
    }
}

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    fireworks.forEach((firework, index) => {
        firework.update();
        firework.draw();

        if (firework.particles.every(p => p.alpha <= 0)) {
            fireworks.splice(index, 1);
        }
    });

    requestAnimationFrame(animate);
}

setInterval(() => {
    const x = Math.random() * canvas.width;
    const y = Math.random() * canvas.height / 2;
    fireworks.push(new Firework(x, y));
}, 500);

animate();