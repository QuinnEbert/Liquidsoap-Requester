Liquidsoap Requester -- PHP web app to control your Liquidsoap Audio Stream Generator

Requirements:

 * PHP of version 5 or later (PHP v4 will not be supported, get with the times!)
 * Liquidsoap Audio Stream Generator (for now version 0.9 is "certified" to run, 1.0 will start up, but see below notes).
 * (Preferred) Apache web server of version 2.x.x, 1.x.x untested (but may run).

Notes about Liquidsoap 1.0:

 * On 12th March 2014, I tested the example script with Liquidsoap 1.0.1, as provided by Debian 7's default repositories.  The script seemed to start appropriately, queueing of tracks worked, but I was less than impressed with the reliability of playback order--I will be looking into this.

See "TODO.txt" for list of planned upcoming changes.