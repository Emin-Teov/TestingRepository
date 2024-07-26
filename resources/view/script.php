<script>
    const body = document.querySelector("body");
    const emailValue = "e1000.tavakkulov@gmail.com";
    const emailCopy = "<?=$c["copy"]?>";

    <?php if ($test_controller->getCount()): ?>
    const searcher = body.querySelector("#searcher");
    const table = body.querySelector("table");
    const searchTable = table.querySelector("tbody").querySelectorAll("tr");
    const form = body.querySelector("#main_form");

    searcher && searcher.addEventListener("input", ()=>{
        const input = searcher.value.toUpperCase();
        searchTable.forEach(tr => {
            let trStyle = "none"; 
            tr.querySelectorAll("p.search").forEach(p => {
                const trValue = p.innerText || p.textContent;
                if(trValue.toUpperCase().includes(input)){ trStyle = ""; }
            });
            tr.style.display = trStyle;
        });
    });
    
        <?php if(Factor::getUser()["permission"]): ?>
        const setTestItems = table.querySelectorAll(".set_test_item");
        var deleteId;
        <?php $tableDeleteId = rand(); ?>

        /** Create delete modal */
        function delete_modal(id){
            setTestItems.disabled = true;
            deleteId = id;
            body.innerHTML += `<?=Factor::setMessage($c["delete_data"], [$c["yes"]=>["onmousedown"=>"delete_item()", "id"=>"close_modal"]], $tableDeleteId)?>`;
        }

        /** Delete from database */
        function delete_item(){
            body.querySelector("#message_modal-<?=$tableDeleteId?>").remove();
            const xhttp = new XMLHttpRequest();
            xhttp.onload = ()=>{ 
                if(setTestItems.length == 1){
                    body.querySelector("table").remove();
                    body.innerHTML += `<?=Factor::setMessage($c["no_data"])?>`;
                }else{
                    body.querySelector(`tr#set_test_item_by_id-${deleteId}`).remove();
                }
            }
            xhttp.open("POST", "index.php?url=removetest");
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(`_token=<?=$_token?>&id=${deleteId}&submit='removeById`);
        }

        /** Set update item */
        function update_item(id, value, description){
            const set_update_id = document.createElement("input");
            form.appendChild(set_update_id);
            set_update_id.setAttribute("type", "hidden");
            set_update_id.setAttribute("name", "id");
            set_update_id.setAttribute("value", id);

            form.setAttribute("action", "./edittest");
            form.querySelector("[name='value']").value = value;
            form.querySelector("[name='description']").value = description;
            form.querySelector("[name='submit']").value = "UPDATE";
        }
        <?php endif; ?>
    <?php endif; ?>
</script>