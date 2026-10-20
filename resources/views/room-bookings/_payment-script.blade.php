@push('scripts')
<script>
    // Show the card fields only when "Card machine" is chosen
    document.querySelectorAll('.pay-method').forEach(radio => {
        const update = () => {
            const box = document.getElementById(radio.dataset.box);
            const card = document.querySelector(`.pay-method[value="card"][data-box="${radio.dataset.box}"]`);
            if (box && card) box.style.display = card.checked ? '' : 'none';
        };
        radio.addEventListener('change', update);
        update();
    });
</script>
@endpush
