from rapidocr_onnxruntime import RapidOCR

engine = RapidOCR()

result, _ = engine(r"D:\LENOVO\Downloads\1.jpeg")

print("RESULT:")
print(result)

print("TYPE:")
print(type(result))