import json,time,os,signal,sys
import wllib
import multiprocessing
from wili.lib import is_connected, WILI_COLOR, PACKAGE_LOCATION

class Main(object):
    DPath = ''
    DUrl = ''
    wlconf = ''
    wlconffile = None
    last_modified_wl = None
    lineDict = {}
    rblList = []
    p = None

    KIV = '# # # # # '\
            + '##  # ##  '\
            + '# # # #   '

    SETUP = '   ## ### ### # # ###   # # # ### #  '\
          + '   #  ##   #  # # ###   ### # ##  #  '\
          + '  ##  ###  #  ### #      #  # #   #  '

    def __init__(self,strip,settings):
        self.DPath = PACKAGE_LOCATION+'/wldata'
        self.DUrl = 'data.wien.gv.at'

        self.settingsconf = PACKAGE_LOCATION+"settings.conf"
        self.settingsfile = open(self.settingsconf)
        self.settings = json.loads(self.settingsfile.read())
        self.last_modified_settings = os.path.getmtime(self.settingsconf)

        self.wlconf = PACKAGE_LOCATION+"wlconf.conf"
        self.wlconffile = open(self.wlconf)

        self.last_modified_wl = os.path.getmtime(self.wlconf)
        (self.lineDict,self.rblList) = wllib.wlFileChanged(self.wlconf)
        if is_connected(['www.google.com',self.DUrl]):
            os.system('wget http://'+self.DUrl+'/csv/wienerlinien-ogd-haltestellen.csv -O '+PACKAGE_LOCATION+'/wldata/wienerlinien-ogd-haltestellen.csv > /dev/null 2>&1 & wget http://'+self.DUrl+'/csv/wienerlinien-ogd-linien.csv -O '+PACKAGE_LOCATION+'/wldata/wienerlinien-ogd-linien.csv > /dev/null 2>&1 & wget http://'+self.DUrl+'/csv/wienerlinien-ogd-steige.csv -O '+PACKAGE_LOCATION+'/wldata/wienerlinien-ogd-steige.csv > /dev/null 2>&1')
        else:
            wllib.displayABC(strip, self.SETUP, WILI_COLOR, 1)

    def loop(self,strip,settings):
        if os.path.getmtime(self.wlconf) != self.last_modified_wl:
            (self.lineDict,self.rblList) = wllib.wlFileChanged(self.wlconf)
            self.last_modified_wl = os.path.getmtime(self.wlconf)
        if os.path.getmtime(self.settingsconf) != self.last_modified_settings:
            self.settings = json.loads(self.settingsfile.read())
            self.last_modified_settings = os.path.getmtime(self.settingsconf)
        if len(self.rblList) == 0:
            time.sleep(0.1)
            return
        t1 = time.time()
        rblArray = wllib.WL.getTime(self.rblList)
        s = time.time()-t1
        if rblArray is None:
            wllib.displayABC(strip, self.KIV, WILI_COLOR, 0)
            time.sleep(0.1)
            return
        if len(rblArray) == 0:
            time.sleep(0.1)
            return
        self.rblTimes = wllib.getTimes(self.lineDict.keys(), rblArray)
        self.nextOnes = wllib.getNextOnes(self.rblTimes,self.lineDict)
        self.p = multiprocessing.Process(target=wllib.showNextOnes, args=(strip, self.nextOnes, self.settings["method"]))
        if self.p.is_alive():
            self.p.terminate()
        self.p.start()
        self.p.join()
        time.sleep(max(1,10-s))

    def cleanup(self):
        for p in multiprocessing.active_children():
            while p.is_alive():
                p.terminate()
                time.sleep(0.1)
        return 0
