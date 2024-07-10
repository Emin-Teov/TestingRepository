const emailTag = body.querySelector("#email-tag");

/** Copy email address */
emailTag.addEventListener("mousedown", ()=>{
    navigator.clipboard.writeText(emailValue);
    emailTag.setAttribute("title", emailCopy);
});
emailTag.addEventListener("mouseup", ()=>{
    emailTag.setAttribute("title", emailValue);
})