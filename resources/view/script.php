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
            tr.querySelectorAll("p").forEach(p => {
                const trValue = p.innerText || p.textContent;
                if(trValue.toUpperCase().includes(input)){ trStyle = ""; }
            });
            tr.style.display = trStyle;
        });
    });
    
        <?php if(Factor::getUser()["permission"]): ?>
        const deleteModal = body.querySelector("#message-modal");
        const deleteForm = body.querySelector("#remove_form");
        var deleteId;
        deleteModal.style.display="none";

        /** Create delete modal */
        function delete_modal(id){
            deleteId = id;
            deleteModal.style.display="flex";
        }

        /** Delete from database */
        function delete_item(){
            const set_delete_id = document.createElement("input");
            deleteForm.appendChild(set_delete_id);
            set_delete_id.setAttribute("type", "hidden");
            set_delete_id.setAttribute("name", "id");
            set_delete_id.setAttribute("value", deleteId);
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