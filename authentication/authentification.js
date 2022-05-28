function getLoginPopUp() {
    return document.getElementById("loginBody");
}

function getRegisterPopUp() {
    return document.getElementById("registerBody");
}

function getCaptchaPopUp() {
    return document.getElementById("captchaBody");
}

function getVideoBody() {
    return document.getElementById("animatedBackground");
}

function getPageIcons() {
    return document.getElementById("pageIconsSelection");
}

function popUpLogin() {
    const loginPopUp = getLoginPopUp();
    hideElement(getRegisterPopUp(), true);
    hideElement(loginPopUp, !loginPopUp.hidden);
}

function popUpRegister() {
    const registerPopUp = getRegisterPopUp();
    hideElement(getLoginPopUp(), true);
    hideElement(registerPopUp, !registerPopUp.hidden);

}

function popUpCaptcha() {
    const captchaPopUp = getCaptchaPopUp();
    hideElement(getRegisterPopUp(), true);
    hideElement(captchaPopUp, !captchaPopUp.hidden);
    fadeBackground(getVideoBody(), !getVideoBody().hidden);
    hideElement(getPageIcons(), !getPageIcons().hidden);
    executeCaptcha();
}

function hideElement(element, hidden) {
    element.hidden = hidden;
    element.style.zIndex = hidden ? -1 : 100;
}

function fadeBackground(element, hidden) {
    const fadedBackground = 'linear-gradient( rgba(0, 0, 0, 0.90), rgba(0, 0, 0, 0.90) )';
    const normalBackground = 'linear-gradient( rgba(0, 0, 0, 0), rgba(0, 0, 0, 0) )';
    element.style.background = !hidden ? fadedBackground : normalBackground;
}

function getRandomNumber(max) {
    return Math.floor(Math.random() * max);
}

function displayCaptchaAnswer() {
    const imageNumber = getRandomNumber(3) + 1; //captcha start at 1

    const image = document.getElementById("answerImage");
    image.setAttribute('src', "../stylesheet/images/captcha_images/captcha" + imageNumber + ".jpeg");

    return imageNumber;
}

function displayCaptchaChoices(validImage) {
    const captchaChoices = document.getElementsByClassName("captchaChoice");
    let generatedNumbers = [];
    for (let imageIndex = 0; imageIndex < captchaChoices.length; imageIndex++) {
        let rdmNumber = getFilteredRandomNumber(captchaChoices.length, generatedNumbers) + 1;
        generatedNumbers.push(rdmNumber - 1);
        console.log(generatedNumbers);
        captchaChoices[imageIndex].setAttribute('src', "../stylesheet/images/captcha_images/captcha" + rdmNumber + ".jpeg");
    }

    if (generatedNumbers.includes(validImage)) {
        return;
    }
}

function getFilteredRandomNumber(limit, ignoredNumbers) {
    const rdmNumber = getRandomNumber(limit);
    // vérifié que rdmNumber n'est pas dans ignoredNumbers
    if (typeof(ignoredNumbers) != "object") {
        return -1;
    }

    const isContained = ignoredNumbers.includes(rdmNumber);
    if (!isContained) {
        return rdmNumber;
    }

    return getFilteredRandomNumber(limit, ignoredNumbers);
}


function verifyCaptcha(userChoice) {
    const answer = document.getElementById("answerImage").getAttribute("src");
    let error = "Restart"
    let isValid = false;

    if (userChoice.firstChild.getAttribute("src") === answer) {
        popUpCaptcha();
        error = "Success"
        isValid = true;
        document.getElementById("registerButton").setAttribute("onclick", "popUpRegister()");
        popUpRegister();
    }

    const info = document.getElementById("infoPanel");
    const alertColor = isValid ? 'alert-success' : 'alert-danger';

    info.innerHTML = '<div class="alert mt-4 pb-1 ' + alertColor + '" role="alert">' + error + '</div>'

}

function executeCaptcha() {
    const answerImageNumber = displayCaptchaAnswer();
    displayCaptchaChoices(answerImageNumber);
    document.getElementById("registerButton").setAttribute("onclick", "");
}