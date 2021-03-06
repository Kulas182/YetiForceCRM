{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
    <div id="transferOwnershipContainer" class='modelContainer'>
        <div class="modal-header contentsBackground">
            <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
            <h3 id="massEditHeader">{vtranslate('LBL_ChangeType', $MODULE)}</h3>
        </div>
		<div class="alert alert-block alert-error fade in" style="margin: 5px;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<p>{vtranslate('Alert_ChangeType_desc', $MODULE)}</p>
		</div>
        <form class="form-horizontal" id="ChangeType" name="ChangeType" method="post" action="index.php">
            <div class="modal-body tabbable">
                <div class="control-group">
                    <div class="control-label" style="width: 50;">{vtranslate('LBL_SELECT_TYPE',$MODULE)}</div>
                    <div class="controls">
                        <select class="select2-container columnsSelect" id="mail_type" name="mail_type" style="width: 350px;">
                            {foreach key=key item=item from=$TYPE_LIST}
								<option value="{$key}">{vtranslate( $item,$MODULE )}</option>
                            {/foreach}
                        </select>
                    </div></br>
                </div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}
