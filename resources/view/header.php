<?php defined('EXEC') or die; ?>
<header>
    <nav>
        <div class="nav-container">
            <?php if(Factor::getUser()["permission"]): ?>
            <form action="./userexit" method="post">
                <input type="hidden" name="_token" value="<?=$_token;?>">
                <label>
                    <input type="submit" value="<?=$c["logout"]?>" class="btn" name="submit">
                </label>
            </form>
            <?php else: ?>
            <form action="./userenter" method="post">
                <input type="hidden" name="_token" value="<?=$_token?>">
                <label>
                    <input type="submit" value="<?=$c["login"]?>" class="btn" name="submit">
                </label>
                <label>
                    <span class="icon">
                        <svg
                        class="w-6 h-6 text-gray-800 dark:text-white"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        width="30"
                        height="30"
                        fill="none"
                        viewBox="0 0 24 24"
                        >
                        <path
                            stroke="currentColor"
                            stroke-width="1.25"
                            d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        ></path>
                        </svg>
                    </span>
                    <input required
                        maxlength="55"
                        type="text"
                        placeholder="<?=$c["name"]?>"
                        autocomplete="off"
                        name="username"
                    />
                </label>
                <label>
                    <span class="icon">
                        <svg
                        class="w-6 h-6 text-gray-800 dark:text-white"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        width="30"
                        height="30"
                        fill="none"
                        viewBox="0 0 24 24"
                        >
                        <path
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.25"
                            d="M8 10V8c0-2.761 1.239-5 4-5s4 2.239 4 5v2M3.5 17.8v-4.6c0-1.12 0-1.68.218-2.107a2 2 0 0 1 .874-.875c.428-.217.988-.217 2.108-.217h10.6c1.12 0 1.68 0 2.108.217a2 2 0 0 1 .874.874c.218.428.218.988.218 2.108v4.6c0 1.12 0 1.68-.218 2.108a2 2 0 0 1-.874.874C18.98 21 18.42 21 17.3 21H6.7c-1.12 0-1.68 0-2.108-.218a2 2 0 0 1-.874-.874C3.5 19.481 3.5 18.921 3.5 17.8"
                        />
                        </svg>
                    </span>
                    <input required
                        maxlength="55"
                        type="password"
                        placeholder="<?=$c["password"]?>"
                        autocomplete="off"
                        name="password"
                    />
                </label>
            </form>
            <?php endif; ?>
    
            <?php if ($test_controller->getCount()): ?>
            <label>
                <input type="text"  id="searcher" placeholder="<?=$c["search"]?>">
            </label>
            <?php endif; ?>


            <form action="./setlang" id="lang-form" name="lang-form" method="post">
                <input type="hidden" name="_token" value="<?=$_token;?>">
                <input type="hidden" name="lang" id="set-lang">
                <input type="hidden" name="submit">
                <div class="lang-btn">
                <?php foreach ($lang_btn as $btn): ?>
                    <?php if($btn !== Factor::getLang()): ?>
                    <input onclick="this.form.querySelector('#set-lang').value='<?=$btn?>'" type="image" src="resources/flags/<?=$btn?>.png" alt="submit" >
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            </form>
        </div>
    </nav>
</header>