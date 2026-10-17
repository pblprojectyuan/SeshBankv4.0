          <form method="post" action="index.php?route=logout">
            <input type="hidden" name="csrf_token" value="<?= escape(\SeshBank\Security\Csrf::token()) ?>" />
            <button class="logout-button" type="submit">Log out</button>
          </form>
