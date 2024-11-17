<canvas id="user-growth-chart"></canvas>
<script>
    const ctx = document.getElementById('user-growth-chart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: @json($this->getData()),
    });
</script>
