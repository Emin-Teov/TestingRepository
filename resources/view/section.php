<?php defined('EXEC') or die; ?>
<link rel="stylesheet" href="resources/css/table.css">
<section>
<?php if ($test_controller->getCount()): ?>
    <table>
        <thead>
            <th><h3><?=$c["values"]?></h3></th>
            <th><h3><?=$c["description"]?></h3></th>
            <th><h3><?=$c["created"]?></h3></th>
            <th><h3><?=$c["updated"]?></h3></th>
        </thead>
        <tbody>
        <?php foreach ($test_controller->getAll() as $id => $row) : ?>
            <tr>
                <td>
                    <div class="edit-container">
                        <?php if(Factor::getUser()["permission"]): ?>
                        <div class="edit-item">
                            <button type="button" class="update-item" onmousedown='update_item(<?=$id?>, "<?=$row["value"]?>", "<?=$row["description"]?>")'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 1792 1408">
                                    <path fill="currentColor" d="m888 1056l116-116l-152-152l-116 116v56h96v96zm440-720q-16-16-33 1L945 687q-17 17-1 33t33-1l350-350q17-17 1-33m80 594v190q0 119-84.5 203.5T1120 1408H288q-119 0-203.5-84.5T0 1120V288Q0 169 84.5 84.5T288 0h832q63 0 117 25q15 7 18 23q3 17-9 29l-49 49q-14 14-32 8q-23-6-45-6H288q-66 0-113 47t-47 113v832q0 66 47 113t113 47h832q66 0 113-47t47-113V994q0-13 9-22l64-64q15-15 35-7t20 29m-96-738l288 288l-672 672H640V864zm444 132l-92 92l-288-288l92-92q28-28 68-28t68 28l152 152q28 28 28 68t-28 68"/>
                                </svg>
                            </button>

                            <button type="button" class="delete-item" onmousedown='delete_modal(<?=$id?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                    <path fill="currentColor" fill-rule="evenodd" d="m18.412 6.5l-.801 13.617A2 2 0 0 1 15.614 22H8.386a2 2 0 0 1-1.997-1.883L5.59 6.5H3.5v-1A.5.5 0 0 1 4 5h16a.5.5 0 0 1 .5.5v1zM10 2.5h4a.5.5 0 0 1 .5.5v1h-5V3a.5.5 0 0 1 .5-.5M9 9l.5 9H11l-.4-9zm4.5 0l-.5 9h1.5l.5-9z"/>
                                </svg>
                            </button>
                        </div>
                        <?php endif; ?>
                        <p class="search"><?=$row["value"]?></p>
                    </div>
                </td>
                <td><p class="search"><?=$row["description"]?></p></td>
                <td><p class="search"><?=date("Y-m-d h:i:s", $row["created_at"])?></p></td>
                <td><p <?php if($row["updated_at"]): ?> class="search" <?php endif; ?> ><?php echo $row["updated_at"] ? date("Y-m-d h:i:s", $row["updated_at"]) : $c["no_update"];?></p></td>
            </tr>
        <?php endforeach; ?>
        <?php if(Factor::getUser()["permission"]): ?>
             <form action="./removetest" method="post" id="remove_form">
                <input type="hidden" name="_token" value="<?=$_token?>">
                <input type="hidden" name="submit">
                <?php Factor::setMessage($c["delete_data"], [$c["yes"]=>["onmousedown"=>"delete_item()", "type"=>"submit"]]); ?>
            </form>
        <?php endif; ?>
        </tbody>
    </table>
<?php  else: Factor::setMessage($c["no_data"]); endif; ?>
</section>