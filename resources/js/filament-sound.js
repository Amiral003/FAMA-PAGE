document.addEventListener('DOMContentLoaded', () => {

    setInterval(() => {
        const unread = document.querySelector(
            '[data-notifications-unread]'
        );

        if (unread && unread.innerText > 0) {
            const audio = new Audio('/notification.mp3');
            audio.volume = 0.3;
            audio.play();
        }
    }, 10000); // vérifie toutes les 8 sec
});