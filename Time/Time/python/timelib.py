from wili.lib import *

def setPixelColor(strip,pos,col):
    if pos >= LED_PER_ROW and  pos < 2*LED_PER_ROW:
        pos = 2*LED_PER_ROW - pos%LED_PER_ROW - 1
    strip.setPixelColor(pos,col)

def showSecond(strip,s):
    b = str(bin(s))[2:][::-1]
    for i in range(len(b)):
        if b[i] == '1':
            setPixelColor(strip,i+2*LED_PER_ROW, Color(0,100,0))
        else:
            setPixelColor(strip,i+2*LED_PER_ROW, Color(0,0,0))

def showMinute(strip,m):
    b = str(bin(m))[2:][::-1]
    for i in range(len(b)):
        if b[i] == '1':
            setPixelColor(strip,i+LED_PER_ROW, Color(0,0,100))
        else:
            setPixelColor(strip,i+LED_PER_ROW, Color(0,0,0))

def showHour(strip,h):
    b = str(bin(h))[2:][::-1]
    for i in range(len(b)):
        if b[i] == '1':
            setPixelColor(strip,i, Color(100,0,0))
        else:
            setPixelColor(strip,i, Color(0,0,0))

    # if h == 1:
    #     setPixelColor(strip,1, Color(100,0,0))
    # elif h == 2:
    #     setPixelColor(strip,1+LED_PER_ROW, Color(100,0,0))
    # elif h == 3:
    #     setPixelColor(strip,1+2*LED_PER_ROW, Color(100,0,0))
    # elif h == 4:
    #     setPixelColor(strip,1, Color(100,0,0))
    #     setPixelColor(strip,1+LED_PER_ROW, Color(100,0,0))
    # elif h == 5:
    #     setPixelColor(strip,1+LED_PER_ROW, Color(100,0,0))
    #     setPixelColor(strip,1+2*LED_PER_ROW, Color(100,0,0))
    # elif h >= 6:
    #     setPixelColor(strip,1, Color(100,0,0))
    #     setPixelColor(strip,1+LED_PER_ROW, Color(100,0,0))
    #     setPixelColor(strip,1+2*LED_PER_ROW, Color(100,0,0))
    #     if h == 7:
    #         setPixelColor(strip,0, Color(100,0,0))
    #     elif h == 8:
    #         setPixelColor(strip,LED_PER_ROW, Color(100,0,0))
    #     elif h == 9:
    #         setPixelColor(strip,2*LED_PER_ROW, Color(100,0,0))
    #     elif h == 10:
    #         setPixelColor(strip,0, Color(100,0,0))
    #         setPixelColor(strip,LED_PER_ROW, Color(100,0,0))
    #     elif h == 11:
    #         setPixelColor(strip,LED_PER_ROW, Color(100,0,0))
    #         setPixelColor(strip,2*LED_PER_ROW, Color(100,0,0))
    #     elif h == 12:
    #         setPixelColor(strip,0, Color(100,0,0))
    #         setPixelColor(strip,LED_PER_ROW, Color(100,0,0))
    #         setPixelColor(strip,2*LED_PER_ROW, Color(100,0,0))

def showTime(strip,h,m,s):
    showHour(strip,h)
    showMinute(strip,m)
    showSecond(strip,s)
    strip.show()
