--[[

MAP CO-ORDINATE CALCULATOR

If you update the maps (eg. different resolution, crop, etc.) you need to recalculate

1. Find two points on the map which are clearly identifiable in-game (eg. small islands).

I used Brightwater Lake (the small island to the south) and the entrance to the Sunken Temple.

2. Travel there in-game and find out their in-game coordinates (with the .gps command).

   Note that Y is east-west and X is north-south in the game.
   However in GIMP the X coordinate is given first and Y second, so you have to swap them around.
   So, in GIMP, if you mouse-over the spot to get the coordinates, you put them in as Y, X
   (Y being the first one as it is east/west, that is left/right)


--]]



function calc_coordinates (name, gameX1, gameY1, imageX1, imageY1,
                                 gameX2, gameY2, imageX2, imageY2,
                                 imageWidth, imageHeight)

  local gameDeltaX = math.abs (gameX2 - gameX1)
  local gameDeltaY = math.abs (gameY2 - gameY1)
  local imageDeltaX  = math.abs (imageX2 - imageX1)
  local imageDeltaY  = math.abs (imageY2 - imageY1)

  print ("**", name, "**")
  print ("Game Delta X =", gameDeltaX)
  print ("Game Delta Y =", gameDeltaY)
  print ()
  print ("Map  Delta X =", imageDeltaX)
  print ("Map  Delta Y =", imageDeltaY)
  print ()

  local Xratio = gameDeltaX / imageDeltaX
  local Yratio = gameDeltaY / imageDeltaY

  print ("X ratio =", Xratio)
  print ("Y ratio =", Yratio)

  print ()
  print (string.rep ("-", 40))
  print ("You need this stuff:")
  print ()

  -- left edge is how many pixel our reference point was from the left
  -- multiplied by the ratio factor, and adding in the reference point Y
  -- (either reference point will give the same result)

  local leftEdge = (imageY1 * Yratio) + gameY1
  local topEdge  = (imageX2 * Xratio) + gameX2
  print ("Left edge =", leftEdge)
  print ("Top edge =", topEdge)

  -- now calculate the width and height of the image in game units

  local mapWidth = imageWidth * Yratio
  local mapHeight = imageHeight * Xratio
  print ("Map width (game units) =",  mapWidth)
  print ("Map height (game units) =", mapHeight)

  print ()
  print ("For PHP:")
  print ()

  print ("    " .. string.rep ('-', 10) .. " " .. name .. " " .. string.rep ('-', 10))
  print ("    $mapLeftPoint = " .. leftEdge  .. ';')
  print ("    $mapTopPoint  = " .. topEdge   .. ';')
  print ("    $mapWidth     = " .. mapWidth  .. ';')
  print ("    $mapHeight    = " .. mapHeight .. ';')


end -- calc_coordinates

calc_coordinates ("Eastern Kingdoms",
                  -- Brightwater Lake
                  2484.981,   -- X (in-game)
                  24.705,     -- Y (in-game)
                  410,       -- map X
                  803,       -- map Y
                  -- Sunken Temple entrance
                  -10473.484, -- X (in-game)
                  -3816.771,  -- Y (in-game)
                  2855,       -- map X
                  1530,       -- map Y
                  2000,       -- map width
                  3770)       -- map height

print ()
print (string.rep ("*", 70))
print ()
calc_coordinates ("Kalimdor",
                  -- Darnassus on edge of bridge just in front of bank
                  9945.852,  -- X (in-game)
                  2474.220,   -- Y (in-game)
                  278.4,        -- map X
                  392.1,       -- map Y
                  -- Theramore outside guard tower just up road (next to sign)
                  -3418.184,  -- X (in-game)
                  -4172.095,  -- Y (in-game)
                  2325.5,       -- map X
                  1406.8,       -- map Y
                  2000,       -- map width
                  3770)       -- map height



