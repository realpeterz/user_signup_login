
<form action="" method="post" accept-charset="utf-8" id="login">
  <h2>Login</h2> 
  <?php if ( $this->pc_user->getError() ): ?>
  <div class="error">
  	<?php if ( $this->pc_user->getError() === "IDENTIFY_EMPTY") echo "Username or email is required to log in." ?>
    <?php if ( $this->pc_user->getError() === "PASSWORD_INCORRECT") echo "Incorrect password. Retry!" ?>
    <?php if ( $this->pc_user->getError() === "USER_NOT_FOUND") echo "User doesn't exist. Retry!" ?>
  </div>
  <?php endif; ?>    
  <input type="text" name="identity" placeholder="username or email" value="<?php echo set_value('identity', $this->input->post('identity')) ?>" id="identity" />
  <input type="password" name="password" placeholder="password" value="<?php echo set_value('password', $this->input->post('password')) ?>" id="password" />
  <input type="checkbox" name="remember" id="remember" value="1" /><label for="remember">Keep me logged in</label>
  <button type="submit">Log in</button>
  <p class="bottom-links">Not a user yet? <a href="/signup">sign up</a></p>
     
</form>
            