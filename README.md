Installation Instructions
-------------------------

1) Install the dcac [patch](https://github.com/ut-osa/dcac/blob/master/patches/dcac-kernel-3.5.4.patch) to the linux [3.5.4 kernel](https://www.kernel.org/) and compile.

2) Alternatively, pre-built [docker image](https://registry.hub.docker.com/u/prat0318/dcac-base/) from here can be used.

3) Install apache, MySql and PHP on the system.

4) Create MySql tables using the file `mysql_create`.

5) Create a new user in the system e.g. `sudo adduser user`.

6) `adduser` `user` to `navphp_users` table with home dir as `/home/user`.

7) Give dcac `w` permissions to the home directory for the `user` : `bin/setdattr /home/user "w=.apps.navphp.u.user"`

8) Add php wrapper to `dcac.h` compiled through SWIG as a php module (Available on Request).

9) Copy `navphp` to `/var/www` and restart apache.
