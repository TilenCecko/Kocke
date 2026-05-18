(function () {
    const canvas = document.getElementById('podiumCanvas');
    if (!canvas || !Array.isArray(window.podiumPlayers)) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const places = [
        { number: 2, color: '#dfe3df', side: '#b9beb8', top: '#f4f6f1', height: 150 },
        { number: 1, color: '#ffd34d', side: '#dfa929', top: '#ffe985', height: 230 },
        { number: 3, color: '#e85d47', side: '#bf3f2f', top: '#f48a78', height: 120 },
    ];

    function fitText(text, maxWidth, fontSize, weight) {
        let size = fontSize;
        do {
            ctx.font = `${weight} ${size}px Segoe UI, Arial, sans-serif`;
            if (ctx.measureText(text).width <= maxWidth || size <= 12) {
                return size;
            }
            size -= 1;
        } while (size > 12);

        return size;
    }

    function roundRect(x, y, width, height, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
    }

    function drawBlock(x, y, width, height, place) {
        const depth = Math.max(16, width * 0.08);

        ctx.fillStyle = place.top;
        ctx.beginPath();
        ctx.moveTo(x + depth, y - depth);
        ctx.lineTo(x + width, y - depth);
        ctx.lineTo(x + width - depth, y);
        ctx.lineTo(x, y);
        ctx.closePath();
        ctx.fill();

        ctx.fillStyle = place.side;
        ctx.beginPath();
        ctx.moveTo(x + width, y - depth);
        ctx.lineTo(x + width, y + height - depth);
        ctx.lineTo(x + width - depth, y + height);
        ctx.lineTo(x + width - depth, y);
        ctx.closePath();
        ctx.fill();

        const gradient = ctx.createLinearGradient(x, y, x + width, y + height);
        gradient.addColorStop(0, place.color);
        gradient.addColorStop(1, place.side);
        ctx.fillStyle = gradient;
        ctx.fillRect(x, y, width - depth, height);

        ctx.strokeStyle = 'rgba(255, 255, 255, 0.28)';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, width - depth, height);

        ctx.fillStyle = '#ffffff';
        ctx.font = `900 ${Math.min(92, width * 0.38)}px Segoe UI, Arial, sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.22)';
        ctx.shadowBlur = 8;
        ctx.shadowOffsetY = 5;
        ctx.fillText(place.number, x + (width - depth) / 2, y + height / 2);
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 0;
    }

    function drawPlayer(player, x, y, width, isWinner) {
        if (!player) {
            return;
        }

        roundRect(x, y, width, 74, 16);
        ctx.fillStyle = isWinner ? 'rgba(255, 211, 77, 0.18)' : 'rgba(255, 255, 255, 0.08)';
        ctx.fill();
        ctx.strokeStyle = isWinner ? 'rgba(255, 211, 77, 0.55)' : 'rgba(255, 255, 255, 0.14)';
        ctx.stroke();

        const name = String(player.name || '').trim();
        const score = `${player.score} točk`;

        const nameSize = fitText(name, width - 24, 20, 800);
        ctx.fillStyle = '#ffffff';
        ctx.font = `800 ${nameSize}px Segoe UI, Arial, sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'alphabetic';
        ctx.fillText(name, x + width / 2, y + 32);

        ctx.fillStyle = 'rgba(255, 255, 255, 0.78)';
        ctx.font = '700 16px Segoe UI, Arial, sans-serif';
        ctx.fillText(score, x + width / 2, y + 56);
    }

    function draw() {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.clearRect(0, 0, rect.width, rect.height);

        const width = rect.width;
        const height = rect.height;
        const isSmall = width < 620;
        const groundY = height - 26;
        const blockGap = isSmall ? 8 : 0;
        const blockWidth = Math.min(isSmall ? width * 0.31 : width * 0.28, 250);
        const totalWidth = blockWidth * 3 + blockGap * 2;
        const startX = (width - totalWidth) / 2;
        const order = isSmall ? [0, 1, 2] : [0, 1, 2];

        ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
        ctx.beginPath();
        ctx.ellipse(width / 2, groundY + 6, Math.min(width * 0.42, 360), 18, 0, 0, Math.PI * 2);
        ctx.fill();

        order.forEach((placeIndex, visualIndex) => {
            const place = places[placeIndex];
            const player = window.podiumPlayers[placeIndex];
            const blockHeight = isSmall ? place.height * 0.74 : place.height;
            const x = startX + visualIndex * (blockWidth + blockGap);
            const y = groundY - blockHeight;
            const cardY = Math.max(8, y - 96);

            drawPlayer(player, x + 6, cardY, blockWidth - 20, place.number === 1);
            drawBlock(x, y, blockWidth, blockHeight, place);
        });
    }

    window.addEventListener('resize', draw);
    draw();
})();
