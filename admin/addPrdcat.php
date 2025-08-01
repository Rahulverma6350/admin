<?php
include('include/db.php');

if(isset($_POST['formsubmit'])){
    $prdcatt = $_POST['prdcat'];
    $subcategoriesArray = $_POST['subcategories']; // This is an array

    // Convert array to a comma-separated string
    $subcategoriesString = implode(", ", $subcategoriesArray);

    // Insert in a single column
    $sql = "INSERT INTO product_categories (category, sub_category) VALUES ('$prdcatt', '$subcategoriesString')";
    $res = mysqli_query($conn, $sql);

    if($res){
        header('location: viewPrdCat.php');
    }
}

include('include/header.php');
?>


<div class="mainHeading">
<h3>Add Products Category</h3>
</div>

<div class="content">

<div class="mainContent">   

<form method="POST" class="formcontainer" enctype="multipart/form-data">
    <label class="formlabel">Product Category</label>
    <input type="text" name="prdcat" class="forminput" required><br><br>

    <label class="formlabel">Product Sub-category</label>

    <div id="subcategory-container">
    <input type="text" name="subcategories[]" class="forminput subcatInput" required>
</div>
<button type="button" onclick="addSubcategory()" class="plusbtn mt-2">+</button><br><br>


    <button type="submit" class="formbutton" name="formsubmit">Submit</button>
</form>

</div>
</div>

   
<?php include('include/footer.php'); ?>
<script>
function addSubcategory() {
    let container = document.getElementById("subcategory-container");

    // Create a wrapper div
    let wrapper = document.createElement("div");
    wrapper.className = "subcatWrapper";
    wrapper.style.display = "flex";
    wrapper.style.alignItems = "center";
    wrapper.style.gap = "10px";
    wrapper.style.marginTop = "5px";

    // Create new input
    let newInput = document.createElement("input");
    newInput.type = "text";
    newInput.name = "subcategories[]";
    newInput.className = "forminput subcatInput";
    newInput.required = true;

    // Create remove (X) button
    let removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.innerHTML = "×";
    removeBtn.style.backgroundColor = "red";
    removeBtn.style.color = "white";
    removeBtn.style.border = "none";
    removeBtn.style.fontSize = "18px";
    removeBtn.style.width = "30px";
    removeBtn.style.height = "30px";
    removeBtn.style.borderRadius = "50%";
    removeBtn.style.cursor = "pointer";
    removeBtn.onclick = function () {
        container.removeChild(wrapper);
    };

    // Append input and button to wrapper
    wrapper.appendChild(newInput);
    wrapper.appendChild(removeBtn);

    // Append wrapper to main container
    container.appendChild(wrapper);
}
</script>
