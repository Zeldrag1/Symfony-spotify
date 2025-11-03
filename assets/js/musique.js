document.querySelectorAll('.btn-love').forEach(btn => {
    btn.addEventListener('click', function () {
        const heart = this.querySelector('.fa');
        const trackItem = this.closest('.track-item');
        const container = trackItem.closest('.tracks'); // parent container
        const isFavoritesPage = container.classList.contains('tracks-favorites');

        const id = this.dataset.trackId;
        const name = this.dataset.trackName;

        fetch('/track/like', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, name: name })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isFavoritesPage) {
                        if (this.classList.contains('act')) {
                            trackItem.remove();
                        }
                    } else {
                        if (this.classList.contains('act')) {
                            this.classList.remove('act');
                            heart.style.color = '#c0c1c3';
                        } else {
                            this.classList.add('act');
                            heart.style.color = '#e3274d';
                        }
                    }
                } else if (data.error) {
                    alert("Erreur : " + data.error);
                }
            })
            .catch(error => console.error('Erreur fetch:', error));
    });
});
