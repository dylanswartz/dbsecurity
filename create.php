<?php require_once ("overall_header.php") ;?>

<h1>Create a New Database</h1>

<div id="placeholder">
    <div id="feedback" class="info">

    </div>
</div>

<form id="createForm" action="process.php?p=create" method="post">
        <label> Database Name <br/>
            <input type="text" class="textfield" id ="databaseName" name="databaseName" value=""/>
        </label>

    <p class="submit">
        <input type="submit" id="createButton" name="createButton" value="Create"/>
    </p>

</form>

<?php require_once ("overall_footer.php") ;?>