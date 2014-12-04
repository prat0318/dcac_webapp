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

Performance Tests
-----------------

|Test case | W/o DCAC | W DCAC | Slowdown |
| --- | --- | --- | --- |
|Login | 0.6s | 3.58s | 5.97X |
|View Directory (ls) | 1.48s | 6.9s | 4.66X |
|View File (cat) | 0.6s | 2.56s | 4.26X |
|Create new File | 0.6s | 3.58s | 5.97X |
|Access shared File | 0.51s | 2.84s | 4.98X |

PS: The Machine which ran with DCAC was not totally comparable with the machine running w/o DCAC. The former was the guest Qemu machine and the latter was the host machine. Even running a quicksort algorithm was slower on the qemu machine by 6X.

|Test case | W/o DCAC | W DCAC | Slowdown |
| --- | --- | --- | --- |
|QuickSort | 1.8s | 12.5s | 6.25X |

Revised Performance Tests
-----------------

Then we build another qemu image from the vanilla linux 3.5.4 version and ran the tests again on both the qemu images. The numbers were much better this time.

|Test case | W/o DCAC | W DCAC | Slowdown |
| --- | --- | --- | --- |
|Login | 5.17s | 3.58s | 1.12X |
|View Directory (ls) | 8.06s | 8.53s | 1.05X |
|View File (cat) | 3.77s | 4.25s | 1.12X |
|Create new File | 5.68s | 6.13s | 1.08X |
|Access shared File | 3.79s | 3.90s | 1.02X |

The slowdown ranged from 2-12% over different test scenarios.
