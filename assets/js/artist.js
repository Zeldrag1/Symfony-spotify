const artistButtons = document.querySelectorAll('.artist-item .btn-love');
if (artistButtons.length === 0) {
    console.log("artist.js : aucun artiste trouvé, script ignoré");
} else {
    artistButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const artistItem = this.closest('.artist-item');
            const containerArtist = artistItem.closest('.artists'); // correspond à la nouvelle classe
            const isFavoritesPageArtist = containerArtist.classList.contains('artist-favorites');

            const id_artist = this.dataset.artistId;
            const name_artist = this.dataset.artistName;
            const heart = this.querySelector('.fa-heart');

            fetch('/artist/like', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id_artist, name: name_artist })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (isFavoritesPageArtist) {
                            artistItem.remove();
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
}
