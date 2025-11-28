import torch
import torch.nn.functional as F
from torchvision import models, transforms
from PIL import Image

# =========================
# LOAD RESNET MODEL
# =========================
def load_resnet(path, num_classes):
    model = models.resnet50(weights=None)
    model.fc = torch.nn.Linear(2048, num_classes)

    state = torch.load(path, map_location="cpu")
    model.load_state_dict(state["model_state_dict"])

    model.eval()
    return model

# =========================
# LOAD EFFICIENTNET MODEL
# =========================
def load_effnet(path, num_classes):
    from torchvision.models import efficientnet_b0
    model = efficientnet_b0(weights=None)
    model.classifier[1] = torch.nn.Linear(1280, num_classes)

    state = torch.load(path, map_location="cpu")
    model.load_state_dict(state["model_state_dict"])

    model.eval()
    return model

# =========================
# INIT MODELS (LOADED ONCE)
# =========================
jenis_resnet = load_resnet("Models/resnet50_best_jenis.pth", 12)
jenis_eff    = load_effnet("Models/efficientnet_b0_best_jenis.pth", 12)

kategori_resnet = load_resnet("Models/resnet50_best_kategori.pth", 2)
kategori_eff    = load_effnet("Models/efficientnet_b0_best_kategori.pth", 2)

# =========================
# LABELS
# =========================
jenis_id = [
    "baterai","biologis","kaca coklat","kardus","pakaian",
    "kaca hijau","logam","kertas","plastik","sepatu",
    "sampah campuran","kaca bening"
]

kategori_id = ["organik", "non-organik"]

# =========================
# TRANSFORM
# =========================
transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize([0.485, 0.456, 0.406],
                         [0.229, 0.224, 0.225])
])

# =========================
# MAIN PREDICT FUNCTION
# =========================
def classify_image(img_path):
    img = Image.open(img_path).convert("RGB")
    x = transform(img).unsqueeze(0)

    with torch.no_grad():
        j1 = F.softmax(jenis_resnet(x), dim=1)
        j2 = F.softmax(jenis_eff(x), dim=1)
        jenis_idx = torch.argmax((j1 + j2) / 2).item()

        k1 = F.softmax(kategori_resnet(x), dim=1)
        k2 = F.softmax(kategori_eff(x), dim=1)
        kategori_idx = torch.argmax((k1 + k2) / 2).item()

    return jenis_id[jenis_idx], kategori_id[kategori_idx]
