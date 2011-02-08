<div class="wrap">

	<div id="icon-options-general" class="icon32"><br /></div>
<h2>Limit Post Creation Per Day</h2>

<form name="form" action="" method="post">
  <p>Enter the number of post each respective type of user can create per day. Enter <strong>-1</strong> for no limits. Note that Administrators have not limitations.</p>

<h3>Role Limitation</h3>

<table class="form-table">
	<?php foreach (array_keys($wp_roles->roles) as $role) { if ($role == 'administrator') continue;?>
	<tr>
		<th><label><?php echo ucfirst($role) ?></label></th>
        <td><input name="role_limits[<?php echo $role ?>]" type="text" value="<?php echo $options['posts_per_role'][$role] ?>" class="samll-text"  />&nbsp;<span class="description">Enter the maximum number of post each <?php echo $role ?> can create per day.</span></td>
	</tr>
    <?php } ?>
</table>

<h3>Optional</h3>
	<p>If you like, you may disable limits for some users just entering their IDs bellow. Note that you must separate each user ID with comma.</p>

<table class="form-table">
	<tr>
		<th><label >Special Users IDs</label></th>
		<td> <input name="txt_special_users" type="text" value="<?php echo get_option('special_users');?>" class="regular-text code" /></td>
	</tr>
</table>


<p class="submit">
	<input type="submit" name="submit" class="button-primary" value="Update" />
</p>
  </form>

</div>
