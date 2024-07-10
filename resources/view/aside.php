<aside>
    <div class="line-bar">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
    <div class="aside-container">
        <h3><?=Factor::getUser()["username"];?></h3>
        <?php if(Factor::getUser()["permission"]): ?>
        <link rel="stylesheet" href="resources/css/form.css">
        <form action="./addtest" method="post" id="main_form">
            <input type="hidden" name="_token" value="<?php echo $_token; ?>">
            <label class="main_label">
                <input type="text" required class="form_input" maxlength="55" name="value" placeholder="Value">
            </label>
            <label class="main_label">
                <input type="text" required class="form_input" name="description" maxlength="255" placeholder="Description">
            </label>
            <label class="main_label">
                <input type="submit" class="form_input btn" value="Add Item" name="submit">
            </label>
        </form>
        <?php endif; ?>
    </div>
</aside>