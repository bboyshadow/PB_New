<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div style="font-family: Arial, sans-serif; color: #333; font-size: 14px; line-height: 1.6;">
	<table style="border-collapse: collapse; width: 100%; max-width: 500px;">
		<tr>
			<td style="padding: 10px 0; border-top: 2px solid #0073aa;">
				<strong style="color: #0073aa; font-size: 16px;">{{user_name}}</strong><br>
				<span style="color: #666;">Charter Specialist</span><br>
				<strong>{{company_name}}</strong>
			</td>
		</tr>
		<tr>
			<td style="padding: 10px 0;">
				📧 <a href="mailto:{{user_email}}" style="color: #0073aa; text-decoration: none;">{{user_email}}</a><br>
				🌐 <a href="{{website}}" style="color: #0073aa; text-decoration: none;">{{website}}</a>
			</td>
		</tr>
		<tr>
			<td style="padding: 10px 0; font-size: 12px; color: #999;">
				<em>Your trusted partner in luxury yacht charters</em>
			</td>
		</tr>
	</table>
</div>
