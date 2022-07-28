Diplomacy is a popular turn based strategy game in which you battle to control Europe; to win you must be diplomatic and strategic.
 
webDiplomacy lets you play Diplomacy online.

--- 

install/README.txt - Installation information.

AGPL.txt - The license webDiplomacy is distributed under.

---

We welcome code contributions for any of the issues on the "soon" milestone. Simply fork the project, and develop a fix in a branch. We accept pull requests that:

* are well tested
* only include one fix per pull request
* keep the code clean and maintainable
* use the same style as the rest of webdip
* keep whitespace changes to a minimum

When writing the text of your pull request, please include:

* The details of the testing that you've performed
* The github issue number that this pull request is a fix for

---

If you get errors for files within /javascript/ it is because some default Apache configurations use this as a shared folder by default. Disable this alias to resolve.

---

http://webdiplomacy.net/ - The official webDiplomacy server.

https://github.com/kestasjk/webDiplomacy - The webDiplomacy github source repository.

---

To get Philippe Paquette's MILA bots working with the base webDip docker install do:
Ensure that the IP address is the IP of the machine hosting docker (there is probably some docker context/network wizardry to do this..)

docker pull public.ecr.aws/n4k3z7o3/webdiplomacy:latest
docker run -d --env API_WEBDIPLOMACY=http://172.21.16.1:43000/api.php --env API_KEY_USER_01=bot1 --env API_KEY_USER_02=bot2 --env API_KEY_USER_03=bot3 --env API_KEY_USER_04=bot4 --env API_KEY_USER_05=bot5 --env API_KEY_USER_06=bot6 public.ecr.aws/n4k3z7o3/webdiplomacy:latest




Kestas J. Kuliukas - kestas@kuliukas.com
