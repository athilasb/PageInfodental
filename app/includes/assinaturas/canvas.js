let canvas = $('#canvas')[0];
let ctx = canvas.getContext('2d');
let pressed = false;
ctx.lineWidth = 2;
ctx.lineCap = 'round';

//calculando a posição do mouse relativo ao bitmap do canvas
//https://stackoverflow.com/questions/17130395/real-mouse-position-in-canvas/17130415#17130415
function getmouse(evt) {
    var rect = canvas.getBoundingClientRect();
    var scalex = canvas.width / rect.width;
    var scaley = canvas.height / rect.height;
    return {
        x: (evt.clientX - rect.left) * scalex,
        y: (evt.clientY - rect.top) * scaley
    };
}
function draw(e) {
    if (!pressed) { return; }
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineTo(getmouse(e).x, getmouse(e).y);
    ctx.stroke();
}
//para mobile
canvas.addEventListener("touchmove", (e) => {
    e.preventDefault();
    console.log(`e.touches[0].clientX: ${e.touches[0].clientX}
            e.touches[0].clientY: ${e.touches[0].clientY}`);
    draw(e.touches[0]);
});
canvas.addEventListener("touchstart", (e) => {
    e.preventDefault(); //impedir o envento de scrool 
    ctx.beginPath();
    pressed = true;
});
canvas.addEventListener("touchend", (e) => {
    pressed = false;
    ctx.stroke();
});

//encontar uma forma de parar de desenhar quando o usuário inicia o desenho mas sai da area do canvas (enquanto o botão ainda está pressionado);
canvas.addEventListener("mousemove", draw);
canvas.addEventListener("mousedown", () => {
    ctx.beginPath();
    pressed = true;
});
canvas.addEventListener("mouseup", (e) => {
    pressed = false;
    ctx.stroke();
});
document.getElementById("canvas-clear").addEventListener("click", () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
});