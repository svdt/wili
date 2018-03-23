import json,operator,time
import numpy as np
import WL
from wili.lib import *

# Maximum time that can be displayed in decimal
LED_DEC = LED_PER_ROW/2-1
# Maximum time that can be displayed in binary
LED_BIN = sum([2**i for i in range(LED_DEC)])

def getRBLSDebug(wlData):
    rblList = []
    for stationID, stationArray in wlData.iteritems():
        rblList += stationArray["RBLS"]
    return list(set(rblList))

def getRBLS(wlData):
    rblList = []
    for stationID, stationArray in wlData.iteritems():
    	for linesID, linesArray in stationArray["LINES"].iteritems():
    		rblList.append(linesArray["RBL"])
    return list(set(rblList))

def getLineList(wlData):
    lineDict = {}
    for stationID, stationArray in wlData.iteritems():
        for linesID, linesArray in stationArray["LINES"].iteritems():
            DIR = '1' if linesArray["DIR"] == "H" else '2'
            lineDict[linesArray["LINE"]+'-'+stationArray["DIVA"]+'-'+DIR] = (linesArray["C"], stationArray["WEG"])
    return lineDict

def filterRBLS(lineList,rblList):
    rblArray = WL.getTime(rblList)
    if rblArray is None:
        displayABC(strip, KIV, Color(0,255,0), 1)
        return []
    rblList = []
    for rbl in rblArray:
        if rbl.line+'-'+rbl.direction in lineList:
            rblList.append(rbl.id)
	return rblList

def wlFileChanged(wlconf):
    wlconffile = open(wlconf)
    wlData = json.loads(wlconffile.read())
    lineDict = getLineList(wlData)
    ## Gib nur RBLs der Linien zurueck
    #return (lineList,getRBLS(wlData))
    ## Gib nur RBLs der Linien zurueck nachdem alle abgefragt wurden
    #return (lineList,filterRBLS(lineList,getRBLSDebug(wlData)))
    ## Gib alle Linien zurueck
    return (lineDict,getRBLSDebug(wlData))

def noTimeLeft(strip, noTime):
    L = 3
    for i in range(4*LED_PER_ROW):
        for l in noTime:
            (r, dirID, color) = l
            stripDir = 1 - 2*dirID
            strip.setPixelColor((r+dirID)*LED_PER_ROW + stripDir*(i%LED_PER_ROW) - dirID, color)
            strip.setPixelColor((r+dirID)*LED_PER_ROW + stripDir*((i-L-1)%LED_PER_ROW) - dirID, Color(0, 0, 0))
        strip.show()
        time.sleep(0.1)
    for i in range(L+1):
        for l in noTime:
            (r, dirID, color) = l
            stripDir = 1 - 2*dirID
            strip.setPixelColor((r+dirID)*LED_PER_ROW + stripDir*((i-L-1)%LED_PER_ROW) - dirID, Color(0, 0, 0))
        strip.show()
        time.sleep(0.1)

def setRow(strip, l, time, method):
    (r, dirID, color) = l
    stripDir = 1 - 2*dirID
    if method == 'dec':
        for i in range(time):
            strip.setPixelColor((r+dirID)*LED_PER_ROW + stripDir*i - dirID, color)
    elif method == 'bin':
        b = str(bin(time))[2:][::-1]
        for i in range(len(b)):
            if b[i] == '1':
                strip.setPixelColor((r+dirID)*LED_PER_ROW + stripDir*i - dirID, color)
            else:
                strip.setPixelColor((r+dirID)*LED_PER_ROW + stripDir*i - dirID, Color(0,0,0))

def showNextOnes(strip, nextOnes, method="dec"):
    noTime = []
    for i in range(LED_COUNT):
        strip.setPixelColor(i, Color(0,0,0))
    for r in range(min(LED_ROW, len(nextOnes))):
        dirID = int(nextOnes[r][0].split('-')[-3]) - 1
        if r%2 == 1:
            dirID = 1 - dirID
        time = int(nextOnes[r][1][0])
        color = hexToRGB(nextOnes[r][1][1])
        (R,G,B) = moreContrast(color[0], color[1], color[2], 0.2)
        l = (r, dirID, Color(G, R, B))
        if time == 0:
            noTime.append(l)
        elif method == 'bin' and time <= LED_BIN:
            setRow(strip, l, time, method)
        elif method == 'dec' and time <= LED_DEC:
            setRow(strip, l, time, method)
    strip.show()
    noTimeLeft(strip, noTime)

def getTimes(lineList, rblArray):
    rblTimes = {}
    for rbl in rblArray:
        if rbl.line+'-'+rbl.station+'-'+rbl.direction in lineList:
            if rbl.line+'-'+rbl.station+'-'+rbl.direction in rblTimes.keys():
                rblTimes[rbl.line+'-'+rbl.station+'-'+rbl.direction] += rbl.time
            else:
                rblTimes[rbl.line+'-'+rbl.station+'-'+rbl.direction] = rbl.time
    return rblTimes

def getNextOnes(array, lineDict):
    nextOnes = {}
    for line in array:
        for time in array[line]:
            walk = int(lineDict[line][1])
            if time >= walk:
                nextOnes[line+'-at-'+str(time)] = (time-walk, lineDict[line][0])
    return sorted(nextOnes.items(), key=operator.itemgetter(1))[:LED_ROW];
