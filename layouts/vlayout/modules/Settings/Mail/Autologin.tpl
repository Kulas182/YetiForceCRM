<div class="container-fluid autologinContainer" style="margin-top:10px;">
	<h3>{vtranslate('LBL_AUTOLOGIN', $QUALIFIED_MODULE)}</h3>&nbsp;{vtranslate('LBL_AUTOLOGIN_DESCRIPTION', $QUALIFIED_MODULE)}<hr>
	{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
	<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active"><a href="#user_list" data-toggle="tab">{vtranslate('LBL_USER_LIST', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#configuration" data-toggle="tab">{vtranslate('LBL_CONFIGURATION', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="user_list">
			<table class="table table-bordered table-condensed themeTableColor userTable">
				<thead>
					<tr class="blockHeader" >
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_RC_USER', $QUALIFIED_MODULE)}</span>
						</th>
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_CRM_USER', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$MODULE_MODEL->getAccountsList() key=KEY item=ITEM}	
						{assign var=USERS value=$MODULE_MODEL->getAutologinUsers($ITEM.user_id)}
						<tr data-id="{$ITEM.user_id}">
							<td><label>{$ITEM.username}</label></td>
							<td>
								<select class="chzn-select users" multiple name="users" style="width: 500px;">
									{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
									<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $USERS)} selected {/if} data-userId="{$CURRENT_USER_ID}">{$OWNER_NAME}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>	
		</div>
		<div class="tab-pane" id="configuration">
			{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('autologin')}
			<div class="row-fluid">
				<div class="span1 pagination-centered">
					<input class="configCheckbox" type="checkbox" name="autologinActive" id="autologinActive" value="1" {if $CONFIG['autologinActive']=='true'}checked=""{/if}>
				</div>
				<div class="span11">
					<label for="autologinActive">{vtranslate('LBL_AUTOLOGIN_ACTIVE', $QUALIFIED_MODULE)}</label>
				</div>
			</div>

		</div>
	</div>
</div>
