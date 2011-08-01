
<form action="" method="post" accept-charset="utf-8" id="signup">
  <h2>Create an Account</h2> 
  <?php if ( $this->pc_user->getError() ): ?>
	<div class="error"><?php echo $this->pc_user->getError(); ?></div>
  <?php endif; ?>
  <p class="field-wrap"><input type="text" name="username" placeholder="username" value="<?= set_value('username', $this->input->post('username')) ?>" id="username" /></p>
  <p class="field-wrap"><input type="text" name="email" placeholder="email" value="<?= set_value('email', $this->input->post('email')) ?>" id="email" /></p>
  <p class="field-wrap"><input type="password" name="password" placeholder="password" value="<?= set_value('password', $this->input->post('password')) ?>" id="password" /></p>
  <input type="checkbox" name="pwdmask" value="1" id="pwdmask" checked="unchecked"><label for="pwdmask">Show password</label>
  <button type="submit" id="signup-bt">Sign up</button>
  <p class="bottom-links">Already a user? <a href="/login">log in</a></p>    
</form>                                         
