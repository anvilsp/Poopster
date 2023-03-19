import random
import sys
import html

class World: # ie Stage 69, Floor 21, World 9-10
    def __init__(self, prefix, stagenumber):
        self.prefix = prefix
        self.stagenumber = stagenumber

stagewords = ["dx", "ro", "splitz", "tnr", "bg", "snr", "monkey", "indie"]
flagwords = ["context", "world"]
world_prefix = ["World", "Floor", "Stage"]
stage_lists = {
    # key is game name, index 0 is the full stage name, index 1 is the first stage half, index 2 is the second stage half
    "dx": ["stagename/smbdx/smbdx-stagename.txt", "stagename/smbdx/smbdx-firsthalf.txt", "stagename/smbdx/smbdx-secondhalf.txt"],
    "ro": ["stagename/rolledout/ro-stagename.txt", "stagename/rolledout/ro-firsthalf.txt", "stagename/rolledout/ro-secondhalf.txt"],
    "tnr": ["stagename/tnr/stagename.txt", "stagename/tnr/firsthalf.txt", "stagename/tnr/secondhalf.txt"],
    "snr": ["stagename/snr/stagename.txt", "stagename/snr/firsthalf.txt", "stagename/snr/secondhalf.txt"],
    "splitz": ["stagename/splitz/stagename.txt", "stagename/splitz/firsthalf.txt", "stagename/splitz/secondhalf.txt"], 
    "bg": ["stagename/bg/stagename.txt", "stagename/bg/firsthalf.txt", "stagename/bg/secondhalf.txt"]
}

stage_firsthalf = []
stage_secondhalf = []
stage_fullname = []

args = sys.argv
check_arg = set(args).intersection(set(stagewords)) # check if there's any overlap between stage parameters and arguments

enable_world = False
enable_context = False

if len(args) == 2: # if there's exactly 1 argument
    # check if the arguments are ones that should halt the program
    if args[1] == "createdby":
        print("Poopster created by @AnvilSP | https://anvilsp.com/poopster")
        exit()
    elif args[1] == "help":
        print("Poopster combines two stage names from the following games (these can be used as parameters): [dx] Deluxe [tnr] Touch & Roll [snr] Step & Roll [splitz] Banana Splitz [ro] Rolled Out! [bg] BALLYGON. Use [world] to display a random stage number, [context] to display full stage names, [monkey] to randomize only stages from Super Monkey Ball, or [indie] for only stages from indie games on the list.")
        exit()
if "world" in args: # world is called for
    enable_world = True
if "context" in args: # context is called for
    enable_context = True

extra_args = sys.argv.copy()
final_seed = ""

# Weed out important flag words from potential seed
for word in list(extra_args):
    if word in stagewords or word in flagwords or word == "api.py":
        extra_args.remove(word)

# Generate potential seed string
for word in list(extra_args):
    final_seed = html.escape(final_seed + word + " ", True)

# If we have a seed, randomize based off of it
if len(final_seed) > 0:
    random.seed(final_seed)

def append_from_txt(arr : list, path : str):
    # append from a text file to the chosen array
    temp = open(path, 'r', encoding="utf8").read().split('\n')
    for i in temp:
        arr.append(i)

def append_stages(game):
    # append full stagenames, first half, and second half of name in order
    append_from_txt(stage_fullname, stage_lists[game][0])
    append_from_txt(stage_firsthalf, stage_lists[game][1])
    append_from_txt(stage_secondhalf, stage_lists[game][2])

def generate_world():
    # for if the 'world' flag is used, generate a stage number to go before the randomized name
    random_prefix = random.randrange(3) # 0 = World, 1 = Floor, 2 = Stage

    if random_prefix == 0:
        # if we roll World, generate the stage number in the World format; up to 10-20 to fit SMBDX conventions
        stagenumber = str(random.randrange(1, 10)) + "-" + str(random.randrange(1, 20))
    else:
        # if we're on a Floor or Stage, generate a number between 1 and 999
        stagenumber = str(random.randrange(1, 999))

    return World(world_prefix[random_prefix], stagenumber)

# Append stages based on arguments
if sys.argv.count("dx") == 1 or sys.argv.count("monkey") == 1 or not check_arg:
    # Super Monkey Ball Deluxe / Banana Mania / 2; args: dx, monkey
    append_stages("dx")
if sys.argv.count("tnr") == 1 or sys.argv.count("monkey") == 1 or not check_arg:
    # Super Monkey Ball: Touch & Roll; args: tnr, monkey
    append_stages("tnr")
if sys.argv.count("snr") == 1 or sys.argv.count("monkey") == 1 or not check_arg:
    # Super Monkey Ball: Step & Roll; args: snr, monkey
    append_stages("snr")
if sys.argv.count("splitz") == 1 or sys.argv.count("monkey") == 1 or not check_arg:
    # Super Monkey Ball: Banana Splitz; args: splitz, monkey
    append_stages("splitz")
if sys.argv.count("ro") == 1 or sys.argv.count("indie") == 1 or not check_arg:
    # Rolled Out!; args: ro, indie
    append_stages("ro")
if sys.argv.count("bg") == 1 or sys.argv.count("indie") == 1 or not check_arg:
    # BALLYGON; args: bg, indie
    append_stages("bg")

# Random generation
world = generate_world()
stage1 = random.randrange(0, len(stage_fullname) - 1)
stage2 = random.randrange(0, len(stage_fullname) - 1)
final_string = ""

# append world if it's called for
if enable_world:
    final_string += world.prefix + " " + world.stagenumber + " - "
# append the stage name
final_string += stage_firsthalf[stage1] + stage_secondhalf[stage2]
# append the stage context if it's called for
if enable_context:
    final_string += ";[" + stage_fullname[stage1] + " and " + stage_fullname[stage2] + "]"

# print the final string
print(final_string)