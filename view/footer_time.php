<nav class="navbar fixed-bottom bg-dark text-light">
        <p>
            <i class="bi bi-clock"></i> <?= (new DateTime(AppTime::get_current_datetime()))->format( 'j/m/Y H:i')?>
        </p>

        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="hour">
            <button type="submit" class="btn btn-outline-secondary text-warning"><i class="bi bi-clock"></i> +1h</button>
        </form>

        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="day">
            <button type="submit" class="btn btn-outline-secondary text-warning"><i class="bi bi-calendar-event"></i> +1d</button>
        </form>

        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="7">
            <input type="hidden" name="unit" value="day">
            <button type="submit" class="btn btn-outline-secondary text-warning"><i class="bi bi-calendar2-week"></i> +1w</button>
        </form>

        <form method="post" action="time/reset" style="display: inline;">
            <button type="submit" class="btn btn-outline-secondary text-danger"><i class="bi bi-arrow-clockwise"></i> Reset</button>
        </form>
</nav>
