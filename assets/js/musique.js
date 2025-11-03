document.querySelectorAll('.btn-love').forEach(btn => {

    btn.addEventListener('click',  function () {
        console.log(this);
        const heart = this.querySelector('.fa');
        const circle = this.querySelector('.circle');
        const ornamentsContainer = this.querySelector('.small-ornament');
        const ornaments = this.querySelectorAll('.ornament');
        const eclipse = this.querySelector('#eclipse');

        if (!this.classList.contains('act')) {
            this.classList.add('act');

            // Reset animation states
            gsap.set([circle, ornamentsContainer], {rotation: 0, scale: 0});
            gsap.set(ornaments, {opacity: 0, scale: 1});

            let tl = gsap.timeline();

            tl.to(heart, {duration: 0.1, scale: 0, ease: "none"})
                .to(circle, {duration: 0.2, scale: 1.2, opacity: 1, ease: "none"})
                .to(heart, {duration: 0.2, delay: 0.1, scale: 1.3, color: '#e3274d', ease: "power1.out"})
                .to(heart, {duration: 0.2, scale: 1, ease: "power1.out"})
                .to(eclipse, {duration: 0.2, strokeWidth: 10, ease: "none"}, "-=0.3")
                .to(eclipse, {duration: 0.2, strokeWidth: 0, ease: "none"}, "-=0.1")
                .to(ornamentsContainer, {duration: 0.3, scale: 0.8, opacity: 1, ease: "linear"})
                .to(ornamentsContainer, {duration: 0.2, scale: 1.2, opacity: 1, rotation: 15, ease: "power1.out"})
                .to(ornaments, {duration: 0.2, opacity: 1, ease: "none"})
                .to(ornaments, {duration: 0.1, scale: 0, ease: "power1.out"});

        } else {
            this.classList.remove('act');
            gsap.set(heart, {color: '#c0c1c3'});
        }

        const id = this.dataset.trackId;
        const name = this.dataset.trackName;

        const response =  fetch('/track/like', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({'id':id, 'name':name})
        });
    });
});

