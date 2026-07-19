import Modal from '@/Components/Modal'
import { X } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'
import * as THREE from 'three'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { STLLoader } from 'three/examples/jsm/loaders/STLLoader.js'

interface ModelPreviewModalProps {
    show: boolean
    onClose: () => void
    name: string
    url: string
}

function frameObject(object: THREE.Object3D, camera: THREE.PerspectiveCamera, controls: OrbitControls) {
    const box = new THREE.Box3().setFromObject(object)
    const size = box.getSize(new THREE.Vector3())
    const center = box.getCenter(new THREE.Vector3())
    object.position.sub(center)

    const maxDim = Math.max(size.x, size.y, size.z) || 1
    const distance = maxDim * 2

    camera.position.set(distance, distance, distance)
    camera.near = maxDim / 100
    camera.far = maxDim * 100
    camera.updateProjectionMatrix()
    controls.target.set(0, 0, 0)
    controls.update()
}

export default function ModelPreviewModal({ show, onClose, name, url }: ModelPreviewModalProps) {
    const containerRef = useRef<HTMLDivElement>(null)
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        if (!show) return
        const container = containerRef.current
        if (!container) return

        setError(null)

        const width = container.clientWidth || 640
        const height = container.clientHeight || 420

        const scene = new THREE.Scene()
        scene.background = new THREE.Color('#f1f5f9')

        const camera = new THREE.PerspectiveCamera(50, width / height, 0.01, 10000)
        const renderer = new THREE.WebGLRenderer({ antialias: true })
        renderer.setSize(width, height)
        renderer.setPixelRatio(window.devicePixelRatio)
        container.appendChild(renderer.domElement)

        scene.add(new THREE.AmbientLight(0xffffff, 0.7))
        const directional = new THREE.DirectionalLight(0xffffff, 0.8)
        directional.position.set(1, 1.5, 1)
        scene.add(directional)

        const controls = new OrbitControls(camera, renderer.domElement)
        controls.enableDamping = true

        let frameId = 0
        const animate = () => {
            frameId = requestAnimationFrame(animate)
            controls.update()
            renderer.render(scene, camera)
        }

        const extension = name.split('.').pop()?.toLowerCase()

        if (extension === 'stl') {
            new STLLoader().load(
                url,
                (geometry) => {
                    geometry.computeVertexNormals()
                    const material = new THREE.MeshStandardMaterial({ color: '#2563eb', metalness: 0.1, roughness: 0.6 })
                    const mesh = new THREE.Mesh(geometry, material)
                    scene.add(mesh)
                    frameObject(mesh, camera, controls)
                    animate()
                },
                undefined,
                () => setError("Impossible de charger l'aperçu 3D."),
            )
        } else {
            new GLTFLoader().load(
                url,
                (gltf) => {
                    scene.add(gltf.scene)
                    frameObject(gltf.scene, camera, controls)
                    animate()
                },
                undefined,
                () => setError("Impossible de charger l'aperçu 3D."),
            )
        }

        return () => {
            cancelAnimationFrame(frameId)
            controls.dispose()
            renderer.dispose()
            scene.traverse((object) => {
                if (object instanceof THREE.Mesh) {
                    object.geometry.dispose()
                    const materials = Array.isArray(object.material) ? object.material : [object.material]
                    materials.forEach((material) => material.dispose())
                }
            })
            if (renderer.domElement.parentElement === container) {
                container.removeChild(renderer.domElement)
            }
        }
    }, [show, url, name])

    return (
        <Modal show={show} onClose={onClose} maxWidth="2xl">
            <div style={{ padding: 24 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
                    <h2 style={{ margin: 0 }}>{name}</h2>
                    <button type="button" onClick={onClose} style={{ border: 0, background: 'none', cursor: 'pointer' }} aria-label="Fermer"><X size={18} /></button>
                </div>
                {error ? (
                    <div className="rf-empty"><p>{error}</p></div>
                ) : (
                    <div ref={containerRef} style={{ width: '100%', height: 420, borderRadius: 8, overflow: 'hidden' }} />
                )}
                <p className="rf-field-hint" style={{ marginTop: 8 }}>Cliquez-glissez pour tourner, molette pour zoomer.</p>
            </div>
        </Modal>
    )
}
