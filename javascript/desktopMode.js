//Set whether or not to use desktop mode and call it when the client loads the utility JS file.
function setDesktopMode(){
    var toggle = localStorage.getItem("desktopEnabled");
    var toggleElem = document.getElementById('js-desktop-mode');
    if (toggle == "true") {
        changeCSS(false);
        if(toggleElem !== null) {
            toggleElem.innerHTML = "Disable Desktop Mode";
        }
    } else {
        changeCSS(true);
        if(toggleElem !== null) {
            toggleElem.innerHTML = "Enable Desktop Mode";
        }
    }
}
setDesktopMode();

//This is called when a user clicks the Enable/Disabled desktop mode
function toggleDesktopMode(){
    var toggle = localStorage.getItem("desktopEnabled");
    if (toggle == "true") {
        localStorage.setItem("desktopEnabled", false);
    } else {
        localStorage.setItem("desktopEnabled", true);
    }
    setDesktopMode();
}

//Change the CSS documents between Desktop and Mobile Variants
// TRUE = Mobile Included --- FALSE = Desktop Only
function changeCSS(toggle) {
    if(toggle === false) {
        var oldlinkGlobal = document.getElementById("global-css");
        var newlinkGlobal = document.createElement("link");
        newlinkGlobal.setAttribute("rel", "stylesheet");
        newlinkGlobal.setAttribute("type", "text/css");
        newlinkGlobal.setAttribute("id", "global-css");
        newlinkGlobal.setAttribute("href", cssDirectory + "/desktopOnly/global.css");
        document.getElementsByTagName("head").item(0).replaceChild(newlinkGlobal, oldlinkGlobal);

        var oldlinkHome = document.getElementById("home-css");
        var newlinkHome = document.createElement("link");
        newlinkHome.setAttribute("rel", "stylesheet");
        newlinkHome.setAttribute("type", "text/css");
        newlinkHome.setAttribute("id", "home-css");
        newlinkHome.setAttribute("href", cssDirectory + "/desktopOnly/home.css");
        document.getElementsByTagName("head").item(0).replaceChild(newlinkHome, oldlinkHome);

        var oldlinkGamePanel = document.getElementById("game-panel-css");
        var newlinkGamePanel = document.createElement("link");
        newlinkGamePanel.setAttribute("rel", "stylesheet");
        newlinkGamePanel.setAttribute("type", "text/css");
        newlinkGamePanel.setAttribute("id", "game-panel-css");
        newlinkGamePanel.setAttribute("href", cssDirectory + "/desktopOnly/gamepanel.css");
        document.getElementsByTagName("head").item(0).replaceChild(newlinkGamePanel, oldlinkGamePanel);

        var viewPortTag = document.getElementById("viewport-tag");
        if(viewPortTag !== null) {
            viewPortTag.remove();
        }
    }else{
        var oldlinkGlobal = document.getElementById("global-css");
        var newlinkGlobal = document.createElement("link");
        newlinkGlobal.setAttribute("rel", "stylesheet");
        newlinkGlobal.setAttribute("type", "text/css");
        newlinkGlobal.setAttribute("id", "global-css");
        newlinkGlobal.setAttribute("href", cssDirectory + "/global.css");
        document.getElementsByTagName("head").item(0).replaceChild(newlinkGlobal, oldlinkGlobal);

        var oldlinkHome = document.getElementById("home-css");
        var newlinkHome = document.createElement("link");
        newlinkHome.setAttribute("rel", "stylesheet");
        newlinkHome.setAttribute("type", "text/css");
        newlinkHome.setAttribute("id", "home-css");
        newlinkHome.setAttribute("href", cssDirectory + "/home.css");
        document.getElementsByTagName("head").item(0).replaceChild(newlinkHome, oldlinkHome);

        var oldlinkGamePanel = document.getElementById("game-panel-css");
        var newlinkGamePanel = document.createElement("link");
        newlinkGamePanel.setAttribute("rel", "stylesheet");
        newlinkGamePanel.setAttribute("type", "text/css");
        newlinkGamePanel.setAttribute("id", "game-panel-css");
        newlinkGamePanel.setAttribute("href", cssDirectory + "/gamepanel.css");
        document.getElementsByTagName("head").item(0).replaceChild(newlinkGamePanel, oldlinkGamePanel);

        var viewPortTag = document.createElement("meta");
        viewPortTag.setAttribute("id", "viewport-tag");
        viewPortTag.setAttribute("name", "viewport");
        viewPortTag.setAttribute("content", "width=device-width, initial-scale=1");
        document.getElementsByTagName("head").item(0).appendChild(viewPortTag);
    }
}