const emailTag = body.querySelector("#email-tag");
const messagesModules = body.querySelectorAll(".message_modal");

/** Set position of warning modules */
if(messagesModules.length > 1){
    for (let index = 0; index < messagesModules.length; index++) {
        messagesModules[index+1].style.top = `${messagesModules[index+1].offsetTop + messagesModules[index].offsetHeight +15}px`;
    }
}

/** Copy email address */
emailTag.addEventListener("mousedown", ()=>{
    navigator.clipboard.writeText(emailValue);
    emailTag.setAttribute("title", emailCopy);
});
emailTag.addEventListener("mouseup", ()=>{
    emailTag.setAttribute("title", emailValue);
})