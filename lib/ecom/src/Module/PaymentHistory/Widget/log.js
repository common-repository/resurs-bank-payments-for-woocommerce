function showExtra(data)
{
    document.getElementById('rb-ph-extra-content').innerHTML = data;
    document.getElementById('rb-ph-log-table').style.display = 'none';
    document.getElementById('rb-ph-extra').style.display = 'block';
}

function showLogTable()
{
    document.getElementById('rb-ph-extra').style.display = 'none';
    document.getElementById('rb-ph-log-table').style.display = '';
}

function showWidget()
{
    document.getElementById('rb-ph-hidden').style.display = 'block';
}

function hideWidget()
{
    document.getElementById('rb-ph-hidden').style.display = 'none';
}

window.onload = () => {
    const phButton = document.querySelector("#rb-ph-button");
    phButton.onclick = showWidget;

    const phCloseButton = document.querySelector("#rb-ph-close-button");
    phCloseButton.onclick = hideWidget;

    const phBackground = document.querySelector("#rb-ph-background");
    phBackground.onclick = hideWidget;

    const phBtnGoBack = document.querySelector('#rb-ph-go-back');
    phBtnGoBack.onclick = showLogTable;

    let phExtraButtons = document.querySelectorAll(".rb-ph-show-extra-btn");
    phExtraButtons.forEach(function (el) {
        el.addEventListener('click', function () {
            showExtra(el.children[0].textContent);
        });
    });
}
