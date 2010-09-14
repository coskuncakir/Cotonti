<!-- BEGIN: MAIN -->

		<!-- BEGIN: PAGEEDIT_ERROR -->
		<div class="error">{PAGEEDIT_ERROR_BODY}</div>
		<!-- END: PAGEEDIT_ERROR -->

		<div class="block">
			<h2 class="page">{PAGEEDIT_PAGETITLE}</h2>
			<form action="{PAGEEDIT_FORM_SEND}" enctype="multipart/form-data" method="post" name="update">
				<table class="cells">
					<tr>
						<td class="width30">{PHP.L.Category}:</td>
						<td class="width70">{PAGEEDIT_FORM_CAT}</td>
					</tr>
					<tr>
						<td>{PHP.L.Title}:</td>
						<td>{PAGEEDIT_FORM_TITLE}</td>
					</tr>
					<tr>
						<td>{PHP.L.Description}:</td>
						<td>{PAGEEDIT_FORM_DESC}</td>
					</tr>
					<tr>
						<td>{PHP.L.Author}:</td>
						<td>{PAGEEDIT_FORM_AUTHOR}</td>
					</tr>
					<tr>
						<td>{PHP.L.Date}:</td>
						<td>{PAGEEDIT_FORM_DATE}</td>
					</tr>
					<tr>
						<td>{PHP.L.Begin}:</td>
						<td>{PAGEEDIT_FORM_BEGIN}</td>
					</tr>
					<tr>
						<td>{PHP.L.Expire}:</td>
						<td>{PAGEEDIT_FORM_EXPIRE}</td>
					</tr>
					<tr>
						<td>{PHP.L.Extrakey}:</td>
						<td>{PAGEEDIT_FORM_KEY}</td>
					</tr>
					<tr>
						<td>{PHP.L.Alias}:</td>
						<td>{PAGEEDIT_FORM_ALIAS}</td>
					</tr>
					<!-- BEGIN: TAGS -->
					<tr>
						<td>{PAGEEDIT_TOP_TAGS}:</td>
						<td>{PAGEEDIT_FORM_TAGS} ({PAGEEDIT_TOP_TAGS_HINT})</td>
					</tr>
					<!-- END: TAGS -->
					<!-- BEGIN: ADMIN -->
					<tr>
						<td>{PHP.L.Owner}:</td>
						<td>{PAGEEDIT_FORM_OWNERID}</td>
					</tr>
					<tr>
						<td>{PHP.L.Parser}:</td>
						<td>{PAGEEDIT_FORM_TYPE}</td>
					</tr>
					<tr>
						<td>{PHP.L.Hits}:</td>
						<td>{PAGEEDIT_FORM_PAGECOUNT}</td>
					</tr>
					<!-- END: ADMIN -->
					<tr>
						<td colspan="2">
							{PAGEEDIT_FORM_TEXT}<!-- IF {PAGEEDIT_FORM_PFS_TEXT_USER} -->{PAGEEDIT_FORM_PFS_TEXT_USER}<!-- ENDIF --><!-- IF {PAGEEDIT_FORM_PFS_TEXT_SITE} --><span class="spaced">{PHP.cfg.separator}</span>{PAGEEDIT_FORM_PFS_TEXT_SITE}<!-- ENDIF -->
						</td>
					</tr>
					<tr>
						<td>{PHP.themelang.pageedit.File}:<br />
						{PHP.themelang.pageadd.Filehint}</td>
						<td>{PAGEEDIT_FORM_FILE}</td>
					</tr>
					<tr>
						<td>{PHP.L.URL}:<br />{PHP.themelang.pageedit.URLhint}</td>
						<td>{PAGEEDIT_FORM_URL}<br />{PAGEEDIT_FORM_PFS_URL_USER} &nbsp; {PAGEEDIT_FORM_PFS_URL_SITE}</td>
					</tr>
					<tr>
						<td>{PHP.themelang.pageedit.Filesize}:<br />{PHP.themelang.pageedit.Filesizehint}</td>
						<td>{PAGEEDIT_FORM_SIZE}</td>
					</tr>
					<tr>
						<td>{PHP.themelang.pageedit.Filehitcount}:<br />{PHP.themelang.pageedit.Filehitcounthint}</td>
						<td>{PAGEEDIT_FORM_FILECOUNT}</td>
					</tr>
					<tr>
						<td>{PHP.themelang.pageedit.Pageid}:</td>
						<td>#{PAGEEDIT_FORM_ID}</td>
					</tr>
					<tr>
						<td>{PHP.themelang.pageedit.Deletethispage}:</td>
						<td>{PAGEEDIT_FORM_DELETE}</td>
					</tr>
					<tr>
						<td colspan="2" class="valid">
						<!-- IF {PHP.usr_can_publish} -->
						<input name="rpublish" type="submit" class="submit" value="{PHP.L.Publish}"
							onclick="this.value='OK';return true" />
						<input type="submit" value="{PHP.L.Putinvalidationqueue}" />
						<!-- ELSE -->
						<input type="submit" class="submit" value="{PHP.L.Update}" />
						<!-- ENDIF -->
						</td>
					</tr>
				</table>
			</form>
		</div>

<!-- END: MAIN -->