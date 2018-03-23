import json,time,os,signal,sys
import timelib
import datetime
from wili.lib import is_connected, WILI_COLOR, PACKAGE_LOCATION, displayABC, lightsOff

class Main(object):

    TIME = '### # ### ###  '\
          +' #  # ### ##   '\
          +' #  # # # ###  '

    def __init__(self,strip,settings):
        self.strip = strip
        displayABC(strip, self.TIME, WILI_COLOR, 1)

    def loop(self,strip,settings):
        t1 = time.time()
        self.strip = strip
        lightsOff(strip)
        now = datetime.datetime.time(datetime.datetime.now())
        timelib.showTime(strip,now.hour,now.minute,now.second)
        time.sleep(0.898-time.time()+t1)

    def cleanup(self):
        lightsOff(self.strip)
        return 0
