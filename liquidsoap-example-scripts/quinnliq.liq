#!/usr/bin/liquidsoap
# 
# ---------------------
# | LIQUIDSOAP SCRIPT |
# ---------------------
# by :David Quinn Ebert
# for:David Quinn Ebert
# ---------------------
# 
# FIXME: I didn't lint-check this before SVN-committing this (let me know if it's broken).
# 
# Enable logging on Standard Output
set("log.stdout", true)
# 
# Enable logging to File "/home/quinn/Liquidsoap/quinnliq.log"
set("log.file",false)
# 
# Allow requests from Telnet (Liquidsoap Requester)
set("server.telnet",true)
set("server.telnet.bind_addr","127.0.0.1")
set("server.telnet.port",1234)
set("server.telnet.reverse_dns",false)

# 
# Quinn's station track auto-playlist
#+ randomized track playback from the playlist path
#+ use files from directory "/home/quinn/Music/"
#+ play a new random track each time LS performs select() 
#+ 90-second timeout on remote track preparation processes
#+ 1.0-hour maximum file length (in case things "run away")
#+ 0.5-hour default file length (in case things "run away")
myplaylist = playlist(id="playlist",length=3600.0, default_duration=1800.0,timeout=90.0,mode="random",reload=300,reload_mode="seconds",mime_type="audio/x-mpegurl","/home/quinn/Music/")
# 
# Request Queue from Telnet (Liquidsoap Requester)
requests = request.queue(id="requests")
# 
# 
# INTERNAL PROCESSES: DO NOT EDIT
# 
# Start building the feed with music
radio = myplaylist
# 
# Add in our on-disk security
radio = fallback(id="switcher",track_sensitive = true, [requests, radio, blank(duration=5.)])
# 
# iTunes-style (so-called "dumb"--but good enough for me) crossfading:
radio = crossfade(start_next=10.,fade_out=10.,fade_in=10.,radio)
# 
# Setup the output Streams:
output.icecast(%mp3(
  samplerate=44100,
  stereo=true,
  bitrate=192),
  id="ToVorbis",
  mount="/myAuto.mp3",
  name="QuinnEbert.NET Radio",
  host="127.0.0.1",
  port=8000,
  password="your_password_here",
  genre="Mixed,Various,Other",
  url="http://www.QuinnEbert.net/",
  description = "Automation Powered By Liquidsoap.  Visit www.QuinnEbert.net for contract-based Liquidsoap setup and customisation!",
  public=true,
  radio)
# 
# END OF SCRIPT
