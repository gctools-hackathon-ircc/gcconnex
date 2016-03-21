<?php

gatekeeper();

$user = elgg_get_page_owner_entity();
$options = array(
	'relationship' => 'member',
	'relationship_guid' => $user->guid,
	'type' => 'group',
	'limit' => false,
);
$plugin = elgg_extract("entity", $vars);
$groups = elgg_get_entities_from_relationship($options);

$change_email_link = "<i><a href='".elgg_get_site_url()."settings/user/'> {$user->email}</a></i>";
$title = elgg_echo('cp_notify:panel_title',array($change_email_link));

$no_notification_available = array('widget','hjforumcategory','messages','MySkill','experience','education','hjforumpost','hjforumtopic','hjforum');	// set all the entities that we want to exclude

// TODO: mentions option to send/receive notification

$content .= "<table id='notificationstable' cellspacing='0' cellpadding='4' width='100%'>";
foreach ($groups as $group) {
	$content .= "<tr>";
	$content .= "	<td class='namefield'> <strong>".elgg_echo('cp_notify:content_name')."</strong> </td>"; // column: name of the group
	$content .= "	<td class='namefield'> <strong>".elgg_echo('cp_notify:email')."</strong> </td>"; // column: send notification by e-mail
	$content .= "	<td class='namefield'> <strong>".elgg_echo('cp_notify:site_mail')."</strong> </td>"; // column: send notification by site mail


	$cpn_set_subscription_email = $plugin->getUserSetting("cpn_email_{$group->getGUID()}", $user->getGUID());	// setting email notification
	// (email) if user set item to subscribed, and no relationship has been built, please add new relationship
	if ($cpn_set_subscription_email === elgg_echo("cp_notify:subscribe") && !check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$group->getGUID()))
		add_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $group->getGUID());
	// (email) if user set item to unsubscribe, update relationship table
	if ($cpn_set_subscription_email === elgg_echo("cp_notify:unsubscribe") && check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$group->getGUID()))
		remove_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $group->getGUID());
	if (empty($cpn_set_subscription_email)) $cpn_set_subscription_email = elgg_echo("cp_notify:unsubscribe");	// if not set, set no email as default


	$cpn_set_subscription_site_mail = $plugin->getUserSetting("cpn_site_mail_{$group->getGUID()}", $user->getGUID());
	// (site mail)
	if ($cpn_set_subscription_site_mail === elgg_echo("cp_notify:subscribe") && !check_entity_relationship($user->getGUID(), 'cp_subscribed_to_site_mail',$group->getGUID()))
		add_entity_relationship($user->getGUID(), 'cp_subscribed_to_site_mail', $group->getGUID());
	// (site mail)
	if ($cpn_set_subscription_site_mail === elgg_echo("cp_notify:unsubscribe") && check_entity_relationship($user->getGUID(), 'cp_subscribed_to_site_mail',$group->getGUID()))
		remove_entity_relationship($user->getGUID(), 'cp_subscribed_to_site_mail', $group->getGUID());	
	if (empty($cpn_set_subscription_site_mail)) $cpn_set_subscription_site_mail = elgg_echo("cp_notify:unsubscribe");



	$options = array(
		'container_guid' => $group->guid,
		'type' => 'object',
		'limit' => false,
	);
	$group_contents = elgg_get_entities($options);

	$dropdown_options = array (	// labels for dropdown
		"subscribe" => elgg_echo("cp_notify:subscribe"),
		"unsubscribe" => elgg_echo("cp_notify:not_subscribed")
	);

	if (check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $group->getGUID()))
		$cpn_set_grp_subscription_email = elgg_echo("cp_notify:subscribe");
	else
		$cpn_set_grp_subscription_email = elgg_echo("cp_notify:unsubscribe");


	if (check_entity_relationship($user->getGUID(), 'cp_subscribed_to_site_mail', $group->getGUID()))
		$cpn_set_grp_subscription_site_mail = elgg_echo("cp_notify:subscribe");
	else
		$cpn_set_grp_subscription_site_mail = elgg_echo("cp_notify:subscribe");

	// dropdown inputs (both email and site mail)
	$cpn_grp_email_input = elgg_view('input/dropdown', array(		// external email
		'name' => "params[cpn_email_{$group->getGUID()}]",
		'options_values' => $dropdown_options,
		'value' => $cpn_set_grp_subscription_email,
	));

	$cpn_grp_site_mail_input = elgg_view('input/dropdown', array( 	// internal email
		'name' => "params[cpn_site_mail_{$group->getGUID()}]",
		'options_values' => $dropdown_options,
		'value' => $cpn_set_grp_subscription_site_mail,
	));

	// GROUP CONTENT SUBSCRIPTIONS
	$content .= "<tr>";
	$content .= "<td class='namefield'> <strong> <a href='{$group->getURL()}' id='group-{$group->guid}'>{$group->name}</a> </strong> </td>";
	$content .= "<td class='togglefield'> {$cpn_grp_email_input} </td>";
	$content .= "<td class='togglefield'> {$cpn_grp_site_mail_input} </td>";
	$content .= "</tr>";

	$cp_table_tr_count = 0;
	foreach ($group_contents as $group_content) {
		if (!in_array($group_content->getSubtype(), $no_notification_available)) {
			$cp_table_tr_count++;	// so we can display a message when the user does not have anything subscribed to
			$cpn_set_subscription_email = $plugin->getUserSetting("cpn_email_{$group_content->getGUID()}", $user->getGUID());	// setting email notification
			
			// if user set item to subscribed, and no relationship has been built, please add new relationship
			if ($cpn_set_subscription_email === elgg_echo("cp_notify:subscribe") && !check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$group_content->getGUID()))
				add_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $group_content->getGUID());
			// if user set item to unsubscribe, update relationship table
			if ($cpn_set_subscription_email === elgg_echo("cp_notify:unsubscribe") && check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$group_content->getGUID()))
				remove_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $group_content->getGUID());

			if (empty($cpn_set_subscription_email)) $cpn_set_subscription_email = elgg_echo("cp_notify:unsubscribe");	// if not set, set no email as default

			$cpn_set_subscription_site_mail = $plugin->getUserSetting("cpn_site_mail_{$group_content->getGUID()}", elgg_get_page_owner_guid());
			if (empty($cpn_set_subscription_site_mail)) $cpn_set_subscription_site_mail = elgg_echo("cp_notify:subscribe");


			// get subscribed items that are contained in group
			if (check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$group_content->getGUID()))
				$cpn_set_subscription_email = elgg_echo("cp_notify:subscribe");
			else
				$cpn_set_subscription_email = elgg_echo("cp_notify:unsubscribe");

			// dropdown inputs (both email and site mail)
			$cpn_email_input = elgg_view('input/dropdown', array(
				'name' => "params[cpn_email_{$group_content->getGUID()}]",
				'options_values' => $dropdown_options,
				'value' => $cpn_set_subscription_email,
			));

			$cpn_site_mail_input = elgg_view('input/dropdown', array(	// TODO: implement site-mail notifications
				'name' => "params[cpn_site_mail_{$group_content->getGUID()}]",
				'options_values' => $dropdown_options,
				'value' => $cpn_set_subscription_site_mail,
			));

			$content .= "<tr>";
			$content .= "<td class='togglefield'> <a href='{$group_content->getURL()}'> {$group_content->title} </a> <br/><sup>{$group_content->getSubtype()}</sup> </td>";	// column: content name
			$content .= "<td class='togglefield'> {$cpn_email_input} </td>";	// column: send by e-mail
			$content .= "<td class='togglefield'> {$cpn_site_mail_input} </td>";	// column: send by site-mail

		} // end if
	} // end foreach loop

	$content .= "</tr>";
	$content .= "<tr> <td class='spacercolumn' colspan='3'>&nbsp;</td> </tr>";
}

if ($cp_table_tr_count <= 0)
	$content .= "<tr><td colspan='3'>".elgg_echo('cp_notify:no_group_sub')."</td></tr>";
$content .= "</table>";






// CONTENT SUBSCRIBED BY OTHER USERS
$options = array(
	'relationship' => 'cp_subscribed_to_email',
	'relationship_guid' => $user->guid,
	'inverse_relationship' => false,
	'limit' => 0	// no limit
);
$interested_contents = elgg_get_entities_from_relationship($options);

$cp_table_tr_count = 0;
$content .= "<table id='notificationstable' cellspacing='0' cellpadding='4' width='100%'>";
$content .= "<tr>";
$content .= "	<td class='namefield'> <strong> Subscribed Content </strong> </td>"; // column: name of the group
$content .= "	<td class='namefield'> <strong> E-mail </strong> </td>"; // column: send notification by e-mail
$content .= "	<td class='namefield'> <strong> Site-mail </strong> </td>"; // column: send notification by site mail
$content .= "</tr>";
foreach ($interested_contents as $interested_content) {
	if ($interested_content->owner_guid != $user->guid && !in_array($interested_content->getSubtype(), $no_notification_available) && $interested_content->title && $interested_content->getType() === 'object') {
		$cp_table_tr_count++;

		$cpn_set_subscription_email = $plugin->getUserSetting("cpn_email_{$interested_content->getGUID()}", $user->getGUID());	// setting email notification
		
		// if user set item to subscribed, and no relationship has been built, please add new relationship
		if ($cpn_set_subscription_email === elgg_echo("cp_notify:subscribe") && !check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$interested_content->getGUID()))
			add_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $interested_content->getGUID());
		// if user set item to unsubscribe, update relationship table
		if ($cpn_set_subscription_email === elgg_echo("cp_notify:unsubscribe") && check_entity_relationship($user->getGUID(), 'cp_subscribed_to_email',$interested_content->getGUID()))
			remove_entity_relationship($user->getGUID(), 'cp_subscribed_to_email', $interested_content->getGUID());

		if (empty($cpn_set_subscription_email)) $cpn_set_subscription_email = elgg_echo("cp_notify:unsubscribe");	// if not set, set no email as default

		$cpn_set_subscription_site_mail = $plugin->getUserSetting("cpn_site_mail_{$interested_content->getGUID()}", elgg_get_page_owner_guid());
		if (empty($cpn_set_subscription_site_mail)) $cpn_set_subscription_site_mail = elgg_echo("cp_notify:unsubscribe");


		// dropdown inputs (both email and site mail)
		$cpn_email_input = elgg_view('input/dropdown', array(
			'name' => "params[cpn_email_{$interested_content->getGUID()}]",
			'options_values' => $dropdown_options,
			'value' => $cpn_set_subscription_email,
		));

		$cpn_site_mail_input = elgg_view('input/dropdown', array(	// TODO: implement site-mail notifications
			'name' => "params[cpn_site_mail_{$interested_content->getGUID()}]",
			'options_values' => $dropdown_options,
			'value' => $cpn_set_subscription_site_mail,
		));

		$content_owner = get_user($interested_content->owner_guid);
		$content .= "<tr>";
		$content .= "<td class='namefield'> <a href='{$interested_content->getURL()}'>{$interested_content->title}</a> <br/><sup> Author: {$content_owner->name} / {$interested_content->getSubtype()} </sup> </td>";
		$content .= "<td class='togglefield'> {$cpn_email_input} </td>";
		$content .= "<td class='togglefield'> {$cpn_site_mail_input} </td>";
		$content .= "</tr>";
	}
}

if ($cp_table_tr_count <= 0)
	$content .= "<tr><td colspan='3'>You have not subscribed to any content.</td></tr>";
$content .= "</table>";



echo elgg_extend_view('page/elements/sidebar','cp_notifications/sidebar');
echo elgg_view_module('info', $title, $content);

