<div id="login" class="small-box">
	<form name="login" method="post" action="">
		<input type="hidden" name="action" value="login">
		<h5>USER LOGIN</h5>
		<table>
			<tbody><tr class="alt first">
				<td><label>User Name</label></td>
				<td>
					<input type="text" name="username" class="col_12 column">
				</td>
			</tr>
			<tr>
				<td><label>Password</label></td>
				<td><input type="password" name="pwd" class="col_12 column"></td>
			</tr>
			<tr class="last">
				<td colspan="2">
					<button>Submit</button>
					<a href="<?php echo getPermalink('tinycms/common/register');?>">Register</a>
				</td>
			</tr>
		</tbody></table>
	</form>
</div>