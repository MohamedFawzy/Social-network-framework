<form action="relationship/create/{ID}" method="post">
<select name="relationship_type">
<!-- START relationship_types -->
<option value="{type_id}">{type_name}</option>
<!-- END relationship_types -->
</select>
<input type="submit" name="create" value="Connect with {name}" />
</form>